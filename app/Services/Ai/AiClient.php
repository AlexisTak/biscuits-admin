<?php

namespace App\Services\Ai;

use App\Services\Ai\Enums\AssistantType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AiClient
{
    private const CACHE_TTL = 3600; // 1 heure
    private const MAX_TOKENS = 1000;
    private const TEMPERATURE = 0.7;

    public function generate(
        AssistantType $assistant,
        array $conversationMessages
    ): string {
        // 1) Charger le system prompt (avec cache)
        $systemPrompt = $this->getSystemPrompt($assistant);

        // 2) Construire le payload pour le LLM
        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt,
            ],
        ];

        foreach ($conversationMessages as $message) {
            $messages[] = [
                'role' => $message['role'],
                'content' => $message['content']
            ];
        }

        // 3) Appel au modèle avec retry
        return $this->callProviderApiWithRetry($messages, $assistant);
    }

    /**
     * Récupère le system prompt avec cache
     */
    protected function getSystemPrompt(AssistantType $assistant): string
    {
        $cacheKey = "ai_prompt:{$assistant->value}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($assistant) {
            $promptPath = 'prompts/' . $assistant->promptFilename();
            
            if (!Storage::disk('local')->exists($promptPath)) {
                throw new \RuntimeException("Prompt file not found: {$promptPath}");
            }

            return Storage::disk('local')->get($promptPath);
        });
    }

    /**
     * Appel à l'API avec retry automatique
     */
    protected function callProviderApiWithRetry(array $messages, AssistantType $assistant, int $attempt = 1): string
    {
        try {
            return $this->callProviderApi($messages, $assistant);
        } catch (\Exception $e) {
            if ($attempt < 3) {
                sleep($attempt); // Backoff exponentiel simple
                return $this->callProviderApiWithRetry($messages, $assistant, $attempt + 1);
            }
            
            throw $e;
        }
    }

    /**
     * Appel à l'API du provider (OpenAI/Anthropic/etc.)
     */
    protected function callProviderApi(array $messages, AssistantType $assistant): string
    {
        $provider = config('ai.provider', 'openai'); // 'openai' ou 'anthropic'

        return match ($provider) {
            'openai' => $this->callOpenAI($messages, $assistant),
            'anthropic' => $this->callAnthropic($messages, $assistant),
            default => throw new \RuntimeException("Provider non supporté: {$provider}"),
        };
    }

    /**
     * Appel à OpenAI API
     */
    protected function callOpenAI(array $messages, AssistantType $assistant): string
    {
        $apiKey = config('ai.openai_api_key');
        $model = config('ai.openai_model', 'gpt-4-turbo-preview');

        if (empty($apiKey)) {
            throw new \RuntimeException('OpenAI API key not configured');
        }

        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => self::MAX_TOKENS,
                'temperature' => self::TEMPERATURE,
            ]);

        if (!$response->successful()) {
            \Log::error('OpenAI API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Erreur API OpenAI: ' . $response->status());
        }

        $data = $response->json();
        
        return $data['choices'][0]['message']['content'] ?? 'Pas de réponse';
    }

    /**
     * Appel à Anthropic API (Claude)
     */
    protected function callAnthropic(array $messages, AssistantType $assistant): string
    {
        $apiKey = config('ai.anthropic_api_key');
        $model = config('ai.anthropic_model', 'claude-3-5-sonnet-20241022');

        if (empty($apiKey)) {
            throw new \RuntimeException('Anthropic API key not configured');
        }

        // Extraire le system prompt et les messages
        $systemPrompt = '';
        $conversationMessages = [];

        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $systemPrompt = $message['content'];
            } else {
                $conversationMessages[] = $message;
            }
        }

        $response = Http::timeout(60)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => self::MAX_TOKENS,
                'temperature' => self::TEMPERATURE,
                'system' => $systemPrompt,
                'messages' => $conversationMessages,
            ]);

        if (!$response->successful()) {
            \Log::error('Anthropic API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Erreur API Anthropic: ' . $response->status());
        }

        $data = $response->json();
        
        return $data['content'][0]['text'] ?? 'Pas de réponse';
    }
}