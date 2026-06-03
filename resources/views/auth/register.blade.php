<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account — Gonzales Dental Clinic</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&family=Noto+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh font-sans antialiased bg-gradient-to-br from-teal-50 via-cyan-50 to-sky-100 py-8 px-4">

    <div class="w-full max-w-2xl mx-auto">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('login') }}" class="inline-flex flex-col items-center gap-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 rounded-2xl p-2">
                <img src="{{ \App\Models\SiteSetting::instance()->logoUrl() }}"
                     alt="Gonzales Dental Clinic"
                     class="h-16 w-auto object-contain">
                <p class="text-slate-500 text-sm">Patient Registration</p>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
            <h2 class="font-heading text-xl font-semibold text-slate-900 mb-2">Create your patient account</h2>
            <p class="text-sm text-slate-500 mb-8">Fill in the form below to register. All fields marked <span class="text-red-500">*</span> are required.</p>

            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                @if ($errors->any())
                    <div role="alert" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <p class="text-sm font-semibold text-red-800 mb-1">Please fix the following errors:</p>
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-700">• {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                {{-- Account Details --}}
                <fieldset class="mb-8">
                    <legend class="font-heading text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 w-full">
                        Account Details
                    </legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Full Name <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required
                                   autocomplete="name"
                                   class="input-field {{ $errors->has('name') ? 'border-red-400' : '' }}">
                            @error('name') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Email Address <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                                   autocomplete="email"
                                   class="input-field {{ $errors->has('email') ? 'border-red-400' : '' }}">
                            @error('email') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Password <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input id="password" type="password" name="password" required
                                   autocomplete="new-password"
                                   class="input-field {{ $errors->has('password') ? 'border-red-400' : '' }}">
                            @error('password') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Confirm Password <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required
                                   autocomplete="new-password"
                                   class="input-field">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Phone Number <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required
                                   autocomplete="tel"
                                   class="input-field {{ $errors->has('phone') ? 'border-red-400' : '' }}">
                            @error('phone') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </fieldset>

                {{-- Patient Information --}}
                <fieldset class="mb-8">
                    <legend class="font-heading text-base font-semibold text-slate-900 mb-4 pb-2 border-b border-slate-100 w-full">
                        Patient Information
                    </legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-slate-700 mb-1.5">
                                First Name <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" required
                                   autocomplete="given-name"
                                   class="input-field {{ $errors->has('first_name') ? 'border-red-400' : '' }}">
                            @error('first_name') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Last Name <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" required
                                   autocomplete="family-name"
                                   class="input-field {{ $errors->has('last_name') ? 'border-red-400' : '' }}">
                            @error('last_name') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="date_of_birth" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Date of Birth <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" required
                                   autocomplete="bday"
                                   class="input-field {{ $errors->has('date_of_birth') ? 'border-red-400' : '' }}">
                            @error('date_of_birth') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="gender" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Gender <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <select id="gender" name="gender" required
                                    class="input-field {{ $errors->has('gender') ? 'border-red-400' : '' }}">
                                <option value="">Select gender</option>
                                <option value="male" @selected(old('gender') === 'male')>Male</option>
                                <option value="female" @selected(old('gender') === 'female')>Female</option>
                                <option value="other" @selected(old('gender') === 'other')>Other</option>
                            </select>
                            @error('gender') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Home Address <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input id="address" type="text" name="address" value="{{ old('address') }}" required
                                   autocomplete="street-address"
                                   class="input-field {{ $errors->has('address') ? 'border-red-400' : '' }}">
                            @error('address') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="city" class="block text-sm font-medium text-slate-700 mb-1.5">
                                City <span class="text-red-500" aria-hidden="true">*</span>
                            </label>
                            <input id="city" type="text" name="city" value="{{ old('city') }}" required
                                   autocomplete="address-level2"
                                   class="input-field {{ $errors->has('city') ? 'border-red-400' : '' }}">
                            @error('city') <p role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </fieldset>

                <button type="submit"
                        class="w-full py-3 px-4 bg-cyan-600 text-white font-semibold rounded-xl hover:bg-cyan-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 focus-visible:ring-offset-2 transition-all duration-150 shadow-sm text-sm">
                    Create Patient Account
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-100 text-center">
                <p class="text-sm text-slate-500">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-cyan-600 font-semibold hover:text-cyan-700 hover:underline">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
