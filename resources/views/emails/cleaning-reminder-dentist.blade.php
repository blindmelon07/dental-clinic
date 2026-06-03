<x-mail::message>
# Patient Cleaning Reminder — Action Required

Hi **Dr. {{ $dentist->user->name }}**,

This is an automated reminder from **{{ $clinic->clinic_name ?? config('app.name') }}**. The following patient is due for their 6-month dental cleaning in **5 days**.

<x-mail::panel>
**Patient:** {{ $patient->full_name }}
**Patient ID:** {{ $patient->patient_number }}
**Phone:** {{ $patient->phone }}
**Email:** {{ $patient->email ?? 'Not provided' }}
**Cleaning Due Date:** {{ $dueDate->format('l, F d, Y') }}
</x-mail::panel>

Please reach out to the patient or ensure their appointment is scheduled before their due date.

<x-mail::button :url="url('/admin/patients')" color="primary">
View Patient in Admin Panel
</x-mail::button>

**Suggested actions:**

- Check if the patient already has a cleaning appointment booked
- Contact the patient to schedule if no appointment exists
- Update the patient's next cleaning date after the visit

Thanks,<br>
**{{ $clinic->clinic_name ?? config('app.name') }}** — Automated Reminder System

<x-mail::subcopy>
This is an automated notification sent to dentists at {{ $clinic->clinic_name ?? config('app.name') }}. Do not reply to this email.
</x-mail::subcopy>
</x-mail::message>
