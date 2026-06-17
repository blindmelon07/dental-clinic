<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — Gonzales Dental Clinic</title>
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

            {{-- Icon + heading --}}
            <div class="flex items-center gap-3 mb-2">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h2 class="font-heading text-xl font-semibold text-slate-900">Set a new password</h2>
            </div>
            <p class="text-sm text-slate-500 mb-6">
                Choose a strong password you haven't used before.
            </p>

            <form method="POST" action="{{ route('password.update') }}" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

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
                               value="{{ old('email', $request->email) }}"
                               required
                               autofocus
                               autocomplete="email"
                               aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                               class="input-field {{ $errors->has('email') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : '' }}"
                               placeholder="you@example.com">
                        @error('email')
                            <p role="alert" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{ show: false }">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-sm font-medium text-slate-700">
                                New Password <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <button type="button" @click="show = !show"
                                    class="text-xs text-slate-400 hover:text-cyan-600 transition-colors"
                                    :aria-label="show ? 'Hide password' : 'Show password'">
                                <span x-show="!show">Show</span>
                                <span x-show="show">Hide</span>
                            </button>
                        </div>
                        <input id="password"
                               :type="show ? 'text' : 'password'"
                               name="password"
                               required
                               autocomplete="new-password"
                               aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                               class="input-field {{ $errors->has('password') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : '' }}"
                               placeholder="At least 8 characters">
                        @error('password')
                            <p role="alert" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Confirm New Password <span class="text-red-500" aria-hidden="true">*</span>
                        </label>
                        <input id="password_confirmation"
                               type="password"
                               name="password_confirmation"
                               required
                               autocomplete="new-password"
                               class="input-field"
                               placeholder="Repeat your new password">
                    </div>

                    <button type="submit"
                            class="w-full py-3 px-4 bg-cyan-600 text-white font-semibold rounded-xl hover:bg-cyan-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 focus-visible:ring-offset-2 transition-all duration-150 shadow-sm text-sm">
                        Reset Password
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-100 text-center">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-cyan-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Sign In
                </a>
            </div>
        </div>

    </div>
</body>
</html>
