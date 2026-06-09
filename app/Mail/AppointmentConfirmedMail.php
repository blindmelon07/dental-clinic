<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Appointment $appointment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Appointment Confirmed – ' . $this->appointment->appointment_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appointment-confirmed',
            with: [
                'appointment' => $this->appointment,
                'patient'     => $this->appointment->patient,
                'dentist'     => $this->appointment->dentist,
                'service'     => $this->appointment->service,
                'clinic'      => \App\Models\SiteSetting::instance(),
                'appointmentsUrl' => url('/patient/appointments'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
