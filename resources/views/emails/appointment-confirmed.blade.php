<x-mail::message>
# Your Appointment is Confirmed!

Hi **{{ $patient->first_name }}**,

Great news! Your appointment at **{{ $clinic->clinic_name ?? config('app.name') }}** has been confirmed. We're looking forward to seeing you.

<x-mail::panel>
**Appointment Details**

- **Appointment No.:** {{ $appointment->appointment_number }}
- **Status:** ✅ Confirmed
- **Service:** {{ $service->name ?? '—' }}
- **Dentist:** {{ $dentist->full_name ?? '—' }}
- **Date:** {{ $appointment->appointment_date->format('l, F d, Y') }}
- **Time:** {{ date('g:i A', strtotime($appointment->start_time)) }} – {{ date('g:i A', strtotime($appointment->end_time)) }}
</x-mail::panel>

**Before your visit, please remember to:**

- Arrive 10–15 minutes early to complete any paperwork
- Bring a valid ID
- Inform us of any changes in your medical history or medications
- If you have dental anxiety, let us know — we're here to help

<x-mail::button :url="$appointmentsUrl" color="success">
View My Appointments
</x-mail::button>

If you need to reschedule or cancel, please do so at least 24 hours in advance through your patient portal or by contacting us directly.

We look forward to seeing you!

**{{ $clinic->clinic_name ?? config('app.name') }}**
{{ $clinic->phone ?? '' }}{{ ($clinic->phone && $clinic->email) ? ' | ' : '' }}{{ $clinic->email ?? '' }}

<x-mail::subcopy>
You are receiving this email because you have an appointment at {{ $clinic->clinic_name ?? config('app.name') }}. If you believe this was sent in error, please contact the clinic.
</x-mail::subcopy>
</x-mail::message>
