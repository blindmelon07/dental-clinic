<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Received — Gonzales Dental Clinic</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&family=Noto+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh font-sans antialiased bg-gradient-to-br from-teal-50 via-cyan-50 to-sky-100 flex items-center justify-center p-4">

    <div class="w-full max-w-md">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('login') }}" class="inline-flex flex-col items-center gap-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 rounded-2xl p-2">
                <img src="{{ \App\Models\SiteSetting::instance()->logoUrl() }}"
                     alt="Gonzales Dental Clinic"
                     class="h-16 w-auto object-contain">
                <p class="text-slate-500 text-sm">Patient Registration</p>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8 text-center">
            <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-cyan-50">
                <svg class="w-7 h-7 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h2 class="font-heading text-xl font-semibold text-slate-900 mb-2">Registration received</h2>
            <p class="text-sm text-slate-500 mb-6">
                Thank you for registering with {{ \App\Models\SiteSetting::instance()->clinic_name ?? config('app.name') }}.
                Our staff will review your details and approve your account shortly. We'll email you once you're able to log in.
            </p>

            <a href="{{ route('login') }}"
               class="inline-flex w-full items-center justify-center py-3 px-4 bg-cyan-600 text-white font-semibold rounded-xl hover:bg-cyan-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 focus-visible:ring-offset-2 transition-all duration-150 shadow-sm text-sm">
                Back to Sign In
            </a>
        </div>
    </div>
</body>
</html>
