<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $phone,
        // Not "$subject": that name is already a (non-readonly) property on Mailable.
        public readonly string $contactSubject,
        public readonly string $messageBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Contact Message: ' . $this->contactSubject,
            replyTo: [$this->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-message',
            with: [
                'name'        => $this->name,
                'email'       => $this->email,
                'phone'       => $this->phone,
                'subject'     => $this->contactSubject,
                'messageBody' => $this->messageBody,
                'clinic'      => \App\Models\SiteSetting::instance(),
            ],
        );
    }
}
