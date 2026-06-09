<x-mail::message>
# Appointment Cancelled

Hi **{{ $patient->first_name }}**,

Your appointment at **{{ $clinic->clinic_name ?? config('app.name') }}** has been cancelled. We're sorry for any inconvenience this may have caused.

<x-mail::panel>
**Cancelled Appointment Details**

- **Appointment No.:** {{ $appointment->appointment_number }}
- **Status:** ❌ Cancelled
- **Service:** {{ $service->name ?? '—' }}
- **Dentist:** {{ $dentist->full_name ?? '—' }}
- **Date:** {{ $appointment->appointment_date->format('l, F d, Y') }}
- **Time:** {{ date('g:i A', strtotime($appointment->start_time)) }} – {{ date('g:i A', strtotime($appointment->end_time)) }}
@if($appointment->cancellation_reason)
- **Reason:** {{ $appointment->cancellation_reason }}
@endif
</x-mail::panel>

Your oral health is important to us. We encourage you to book a new appointment at your earliest convenience to keep up with your dental care.

<x-mail::button :url="$bookUrl" color="primary">
Book a New Appointment
</x-mail::button>

If you have any questions or concerns, please don't hesitate to reach out to us directly.

We hope to see you again soon!

**{{ $clinic->clinic_name ?? config('app.name') }}**
{{ $clinic->phone ?? '' }}{{ ($clinic->phone && $clinic->email) ? ' | ' : '' }}{{ $clinic->email ?? '' }}

<x-mail::subcopy>
You are receiving this email because an appointment was cancelled at {{ $clinic->clinic_name ?? config('app.name') }}. If you believe this was sent in error, please contact the clinic.
</x-mail::subcopy>
</x-mail::message>
