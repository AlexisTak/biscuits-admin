<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewContactNotification;

class ContactService
{
    /**
     * Crée un nouveau contact avec détection anti-spam
     */
    public function create(array $data): Contact
    {
        // Vérification anti-spam basique
        if ($this->isSpam($data)) {
            Log::warning('Contact bloqué pour spam', [
                'email' => $data['email'],
                'ip' => $data['ip_address'] ?? null,
            ]);
            
            throw new \Exception('Votre demande a été identifiée comme spam.', 429);
        }

        // Vérification rate-limit par IP
        if ($this->exceedsRateLimit($data['ip_address'] ?? null)) {
            Log::warning('Rate limit dépassé', ['ip' => $data['ip_address']]);
            throw new \Exception('Trop de demandes. Réessayez dans 1 heure.', 429);
        }

        DB::beginTransaction();
        try {
            $contact = Contact::create($data);

            // Log audit
            Log::info('Nouveau contact créé', [
                'contact_id' => $contact->id,
                'email' => $contact->email,
                'ip' => $contact->ip_address,
            ]);

            // Notifier les admins
            $this->notifyAdmins($contact);

            DB::commit();
            return $contact;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création contact', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Détection anti-spam basique
     */
    private function isSpam(array $data): bool
    {
        $message = strtolower($data['message'] ?? '');
        
        // Mots-clés spam courants
        $spamKeywords = ['viagra', 'casino', 'bitcoin', 'lottery', 'prize', 'click here', 'buy now'];
        foreach ($spamKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        // Honeypot
        if (!empty($data['honey'])) {
            return true;
        }

        // Timestamp trop vieux (> 1h)
        if (isset($data['timestamp']) && (time() - $data['timestamp']) > 3600000) {
            return true;
        }

        return false;
    }

    /**
     * Rate limiting : max 3 contacts par IP par heure
     */
    private function exceedsRateLimit(?string $ip): bool
    {
        if (!$ip) return false;

        $count = Contact::where('ip_address', $ip)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return $count >= 3;
    }

    /**
     * Notifier les admins d'un nouveau contact
     */
    private function notifyAdmins(Contact $contact): void
    {
        $admins = User::where('role', 'admin')->get();
        
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewContactNotification($contact));
        }
    }

    /**
     * Marquer comme lu
     */
    public function markAsRead(Contact $contact): void
    {
        $contact->markAsRead();
        
        Log::info('Contact marqué comme lu', [
            'contact_id' => $contact->id,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Archiver un contact
     */
    public function archive(Contact $contact): void
    {
        $contact->archive();
        
        Log::info('Contact archivé', [
            'contact_id' => $contact->id,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Marquer comme spam
     */
    public function markAsSpam(Contact $contact): void
    {
        $contact->markAsSpam();
        
        Log::warning('Contact marqué comme spam', [
            'contact_id' => $contact->id,
            'email' => $contact->email,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Assigner à un utilisateur
     */
    public function assign(Contact $contact, int $userId): void
    {
        $contact->update(['assigned_to' => $userId]);
        
        Log::info('Contact assigné', [
            'contact_id' => $contact->id,
            'assigned_to' => $userId,
        ]);
    }

    /**
     * Ajouter une réponse
     */
    public function addResponse(Contact $contact, array $data): void
    {
        $contact->responses()->create([
            'user_id' => auth()->id(),
            'message' => $data['message'],
            'is_internal_note' => $data['is_internal_note'] ?? false,
        ]);

        if (!($data['is_internal_note'] ?? false)) {
            $contact->markAsResponded();
        }

        Log::info('Réponse ajoutée au contact', [
            'contact_id' => $contact->id,
            'user_id' => auth()->id(),
        ]);
    }
}