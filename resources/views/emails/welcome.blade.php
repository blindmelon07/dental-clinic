<x-mail::message>
# Welcome to {{ $clinic->clinic_name ?? config('app.name') }}!

Hi **{{ $patient->first_name ?? $user->name }}**,

Thank you for registering with **{{ $clinic->clinic_name ?? config('app.name') }}**. Your patient account has been created successfully and you're all set to start booking appointments.

<x-mail::panel>
**Account Details**

- **Name:** {{ $patient->full_name ?? $user->name }}
- **Email:** {{ $user->email }}
- **Patient No.:** {{ $patient->patient_number ?? '—' }}
</x-mail::panel>

You can now log in to your account and book your first appointment at your convenience.

<x-mail::button :url="$bookUrl" color="primary">
Book an Appointment
</x-mail::button>

**What you can do in your account:**

- Book and manage your appointments
- View your dental records and treatment history
- Download and review your invoices
- Update your personal information

If you have any questions, feel free to reach out to us directly.

We look forward to serving you!

**{{ $clinic->clinic_name ?? config('app.name') }}**
{{ $clinic->phone ?? '' }}{{ ($clinic->phone && $clinic->email) ? ' | ' : '' }}{{ $clinic->email ?? '' }}

<x-mail::subcopy>
You are receiving this email because you recently created an account at {{ $clinic->clinic_name ?? config('app.name') }}. If you did not register, please contact us immediately.
</x-mail::subcopy>
</x-mail::message>
