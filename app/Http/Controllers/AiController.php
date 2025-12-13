<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\Ai\AiClient;
use App\Services\Ai\Enums\AssistantType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AiController extends Controller
{
    // Limite de messages par conversation
    private const MAX_MESSAGES_PER_CONVERSATION = 100;
    
    // Limite de caractères par message
    private const MAX_MESSAGE_LENGTH = 4000;

    public function handle(Request $request, string $assistant, AiClient $aiClient)
    {
        // Rate limiting : 10 requêtes par minute par utilisateur
        $userId = $request->user()?->id ?? $request->ip();
        $rateLimitKey = "ai-chat:{$userId}";
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            throw ValidationException::withMessages([
                'message' => ["Trop de requêtes. Réessayez dans {$seconds} secondes."]
            ]);
        }

        RateLimiter::hit($rateLimitKey, 60);

        try {
            $assistantType = AssistantType::from($assistant);
        } catch (\ValueError $e) {
            return response()->json([
                'error' => 'Assistant invalide'
            ], 400);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:' . self::MAX_MESSAGE_LENGTH],
            'conversation_id' => ['nullable', 'integer', 'exists:ai_conversations,id'],
        ]);

        try {
            return DB::transaction(function () use ($request, $data, $assistantType, $aiClient) {
                // 1) Récupérer ou créer la conversation
                if ($data['conversation_id']) {
                    $conversation = AiConversation::find($data['conversation_id']);
                    
                    // Vérifier que la conversation appartient à l'utilisateur
                    if ($conversation->user_id !== $request->user()?->id) {
                        abort(403, 'Accès non autorisé à cette conversation');
                    }

                    // Vérifier le nombre de messages
                    $messageCount = $conversation->messages()->count();
                    if ($messageCount >= self::MAX_MESSAGES_PER_CONVERSATION) {
                        throw ValidationException::withMessages([
                            'message' => ['Cette conversation a atteint la limite de messages. Créez-en une nouvelle.']
                        ]);
                    }
                } else {
                    $conversation = AiConversation::create([
                        'user_id'   => $request->user()?->id,
                        'assistant' => $assistantType->value,
                        'meta'      => [
                            'created_at' => now()->toIso8601String(),
                            'ip' => $request->ip(),
                        ],
                    ]);
                }

                // 2) Enregistrer le message utilisateur
                $userMessage = AiMessage::create([
                    'ai_conversation_id' => $conversation->id,
                    'role'    => 'user',
                    'content' => $data['message'],
                ]);

                // 3) Récupérer l'historique (limité aux 20 derniers messages)
                $history = $conversation->messages()
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get()
                    ->reverse()
                    ->map(fn ($msg) => [
                        'role' => $msg->role,
                        'content' => $msg->content,
                    ])
                    ->toArray();

                // 4) Générer la réponse
                $assistantReply = $aiClient->generate($assistantType, $history);

                // 5) Sauvegarder la réponse IA
                $assistantMessage = AiMessage::create([
                    'ai_conversation_id' => $conversation->id,
                    'role'    => 'assistant',
                    'content' => $assistantReply,
                ]);

                // 6) Mettre à jour les métadonnées de la conversation
                $conversation->update([
                    'meta' => array_merge($conversation->meta ?? [], [
                        'last_message_at' => now()->toIso8601String(),
                        'message_count' => $conversation->messages()->count(),
                    ])
                ]);

                return response()->json([
                    'conversation_id' => $conversation->id,
                    'reply' => $assistantReply,
                    'message_count' => $conversation->messages()->count(),
                ]);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Erreur AI Chat', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'assistant' => $assistant,
            ]);

            return response()->json([
                'error' => 'Une erreur est survenue lors du traitement de votre message.'
            ], 500);
        }
    }

    /**
     * Récupérer l'historique d'une conversation
     */
    public function show(Request $request, int $conversationId)
    {
        $conversation = AiConversation::with(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }])->findOrFail($conversationId);

        // Vérifier que la conversation appartient à l'utilisateur
        if ($conversation->user_id !== $request->user()?->id) {
            abort(403);
        }

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'assistant' => $conversation->assistant,
                'created_at' => $conversation->created_at,
                'meta' => $conversation->meta,
            ],
            'messages' => $conversation->messages->map(fn ($msg) => [
                'id' => $msg->id,
                'role' => $msg->role,
                'content' => $msg->content,
                'created_at' => $msg->created_at,
            ]),
        ]);
    }

    /**
     * Liste des conversations de l'utilisateur
     */
    public function index(Request $request)
    {
        $conversations = AiConversation::where('user_id', $request->user()->id)
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($conversations);
    }

    /**
     * Supprimer une conversation
     */
    public function destroy(Request $request, int $conversationId)
    {
        $conversation = AiConversation::findOrFail($conversationId);

        if ($conversation->user_id !== $request->user()?->id) {
            abort(403);
        }

        $conversation->delete();

        return response()->json(['message' => 'Conversation supprimée']);
    }
}