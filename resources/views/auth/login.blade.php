<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — Gonzales Dental Clinic</title>
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
                <p class="text-slate-500 text-sm">Patient Portal</p>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
            <h2 class="font-heading text-xl font-semibold text-slate-900 mb-6">Sign in to your account</h2>

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                @if ($errors->any())
                    <div role="alert" class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <p class="text-sm font-semibold text-red-800 mb-1">Please fix the following:</p>
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-700">• {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Email Address <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autocomplete="email"
                               autofocus
                               aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
                               aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                               class="input-field {{ $errors->has('email') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : '' }}">
                        @error('email')
                            <p id="email-error" role="alert" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Password <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input id="password"
                               type="password"
                               name="password"
                               required
                               autocomplete="current-password"
                               aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                               class="input-field">
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                            <span class="text-sm text-slate-600">Remember me</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full py-3 px-4 bg-cyan-600 text-white font-semibold rounded-xl hover:bg-cyan-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 focus-visible:ring-offset-2 transition-all duration-150 shadow-sm text-sm">
                        Sign In
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-100 text-center">
                <p class="text-sm text-slate-500">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="text-cyan-600 font-semibold hover:text-cyan-700 hover:underline">Register here</a>
                </p>
            </div>
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            Staff or dentist? <a href="/admin" class="text-cyan-600 hover:underline">Access Admin Panel</a>
        </p>
    </div>
</body>
</html>
