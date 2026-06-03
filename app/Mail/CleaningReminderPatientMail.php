<?php

namespace App\Mail;

use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CleaningReminderPatientMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Patient $patient) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reminder: Your Dental Cleaning is Due in 5 Days',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.cleaning-reminder-patient',
            with: [
                'patient'  => $this->patient,
                'dueDate'  => $this->patient->next_cleaning_due,
                'bookUrl'  => url('/patient/book'),
                'clinic'   => \App\Models\SiteSetting::instance(),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
