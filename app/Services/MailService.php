<?php

namespace App\Services;

use App\Mail\OutboundMail;
use App\Models\NotificationOutbox;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Queue a transactional email through ZeptoMail and record it in the outbox.
     *
     * @param  array<string, mixed>  $data
     */
    public function send(string $template, User|string $recipient, string $subject, array $data = []): NotificationOutbox
    {
        $email = $recipient instanceof User ? $recipient->email : $recipient;
        $user = $recipient instanceof User ? $recipient : null;

        $outbox = NotificationOutbox::query()->create([
            'user_id' => $user?->id,
            'to_email' => $email,
            'subject' => $subject,
            'template' => $template,
            'status' => 'queued',
        ]);

        Mail::to($email)->queue(new OutboundMail($subject, $template, $data));

        return $outbox;
    }

    public function markSent(NotificationOutbox $outbox): void
    {
        $outbox->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);
    }

    public function markFailed(NotificationOutbox $outbox, string $error): void
    {
        $outbox->update(['status' => 'failed', 'error' => $error]);
    }
}