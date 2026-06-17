<x-mail::message>
# New Message from Your Website

You've received a new message via the {{ $clinic->clinic_name ?? config('app.name') }} contact form.

<x-mail::panel>
**From:** {{ $name }}
**Email:** {{ $email }}
**Phone:** {{ $phone ?: '—' }}
**Subject:** {{ $subject }}
</x-mail::panel>

**Message:**

{{ $messageBody }}

You can reply directly to this email to respond to {{ $name }}.

**{{ $clinic->clinic_name ?? config('app.name') }}**
</x-mail::message>
