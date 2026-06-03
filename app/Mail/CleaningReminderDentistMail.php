<?php

namespace App\Mail;

use App\Models\Dentist;
use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CleaningReminderDentistMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Patient $patient,
        public readonly Dentist $dentist,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Patient Cleaning Due: {$this->patient->full_name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.cleaning-reminder-dentist',
            with: [
                'patient' => $this->patient,
                'dentist' => $this->dentist,
                'dueDate' => $this->patient->next_cleaning_due,
                'clinic'  => \App\Models\SiteSetting::instance(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
