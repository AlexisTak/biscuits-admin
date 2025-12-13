<?php

namespace App\Notifications;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewContactNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Contact $contact
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouveau contact reçu')
            ->line("Un nouveau contact a été reçu de {$this->contact->name}.")
            ->line("Service: {$this->contact->service}")
            ->action('Voir le contact', url('/admin/contacts/' . $this->contact->id))
            ->line('Merci !');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'contact_id' => $this->contact->id,
            'name' => $this->contact->name,
            'email' => $this->contact->email,
            'service' => $this->contact->service,
        ];
    }
}