<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . (\App\Models\SiteSetting::instance()->clinic_name ?? config('app.name')) . '!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.welcome',
            with: [
                'user'       => $this->user,
                'patient'    => $this->user->patient,
                'loginUrl'   => url('/login'),
                'bookUrl'    => url('/patient/book'),
                'clinic'     => \App\Models\SiteSetting::instance(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
