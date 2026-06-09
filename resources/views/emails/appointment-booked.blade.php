<x-mail::message>
# Appointment Booked Successfully

Hi **{{ $patient->first_name }}**,

Your appointment has been booked at **{{ $clinic->clinic_name ?? config('app.name') }}**. We will notify you once it is confirmed by our team.

<x-mail::panel>
**Appointment Details**

- **Appointment No.:** {{ $appointment->appointment_number }}
- **Status:** Pending Confirmation
- **Service:** {{ $service->name ?? '—' }}
- **Dentist:** {{ $dentist->full_name ?? '—' }}
- **Date:** {{ $appointment->appointment_date->format('l, F d, Y') }}
- **Time:** {{ date('g:i A', strtotime($appointment->start_time)) }} – {{ date('g:i A', strtotime($appointment->end_time)) }}
</x-mail::panel>

Please arrive **10–15 minutes early** on the day of your appointment. You will receive another email once your appointment is confirmed.

<x-mail::button :url="$appointmentsUrl" color="primary">
View My Appointments
</x-mail::button>

If you need to cancel or reschedule, you can do so through your patient portal up to 24 hours before your appointment.

See you soon!

**{{ $clinic->clinic_name ?? config('app.name') }}**
{{ $clinic->phone ?? '' }}{{ ($clinic->phone && $clinic->email) ? ' | ' : '' }}{{ $clinic->email ?? '' }}

<x-mail::subcopy>
You are receiving this email because you booked an appointment at {{ $clinic->clinic_name ?? config('app.name') }}. If you believe this was sent in error, please contact the clinic.
</x-mail::subcopy>
</x-mail::message>
