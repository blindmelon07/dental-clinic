<x-mail::message>
# Your Dental Cleaning is Due in 5 Days

Hi **{{ $patient->first_name }}**,

This is a friendly reminder from **{{ $clinic->clinic_name ?? config('app.name') }}** that your next dental cleaning appointment is coming up soon.

<x-mail::panel>
**Cleaning Due Date:** {{ $dueDate->format('l, F d, Y') }}

Regular dental cleanings every 6 months help prevent cavities, gum disease, and keep your smile healthy!
</x-mail::panel>

Don't wait — book your cleaning appointment now to secure your preferred schedule with your dentist.

<x-mail::button :url="$bookUrl" color="primary">
Book My Cleaning Now
</x-mail::button>

**Why cleanings matter every 6 months:**

- Remove plaque and tartar buildup that brushing can't reach
- Early detection of cavities and gum problems
- Professional polishing for a brighter smile
- Peace of mind for your overall oral health

If you have any questions or need to reschedule, please contact our clinic directly.

We look forward to seeing you soon!

**{{ $clinic->clinic_name ?? config('app.name') }}**
{{ $clinic->phone ?? '' }} | {{ $clinic->email ?? '' }}

<x-mail::subcopy>
You are receiving this email because you are a registered patient at {{ $clinic->clinic_name ?? config('app.name') }}. If you believe this was sent in error, please contact the clinic.
</x-mail::subcopy>
</x-mail::message>
