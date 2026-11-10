<?php

namespace App\Mail;

use App\Models\NotificationOutbox;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OutboundMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $mailSubject,
        public string $template,
        public array $data = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->mailSubject);
    }

    public function content(): Content
    {
        $view = view()->exists("emails.{$this->template}")
            ? "emails.{$this->template}"
            : 'emails.generic';

        return new Content(view: $view, with: $this->data);
    }

    public function build(): self
    {
        NotificationOutbox::query()
            ->where('to_email', $this->to[0]['address'] ?? '')
            ->where('subject', $this->mailSubject)
            ->latest('id')
            ->first()
            ?->update(['status' => 'sent', 'sent_at' => now()]);

        return $this;
    }
}
