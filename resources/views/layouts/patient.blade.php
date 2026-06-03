<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gonzales Dental Clinic — Patient Portal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&family=Noto+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-teal-50/60 font-sans antialiased">

    {{-- Skip to main content (accessibility) --}}
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <div class="min-h-dvh flex flex-col">

        {{-- Navigation --}}
        <header role="banner">
            <nav role="navigation" aria-label="Main navigation"
                 class="bg-white/95 backdrop-blur-sm shadow-sm border-b border-slate-200 sticky top-0 z-40"
                 x-data="{ mobileOpen: false, userOpen: false }">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">

                        {{-- Brand --}}
                        <div class="flex items-center gap-8">
                            <a href="{{ route('patient.dashboard') }}"
                               class="flex items-center flex-shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 rounded-lg"
                               wire:navigate
                               aria-label="Gonzales Dental Clinic home">
                                <img src="{{ \App\Models\SiteSetting::instance()->logoUrl() }}"
                                     alt="Gonzales Dental Clinic"
                                     class="h-10 w-auto object-contain">
                            </a>

                            {{-- Desktop nav --}}
                            <div class="hidden md:flex items-center gap-1">
                                <a href="{{ route('patient.dashboard') }}"
                                   class="nav-link {{ request()->routeIs('patient.dashboard') ? 'nav-link-active' : '' }}"
                                   wire:navigate
                                   @if(request()->routeIs('patient.dashboard')) aria-current="page" @endif>
                                    Dashboard
                                </a>
                                <a href="{{ route('patient.appointments') }}"
                                   class="nav-link {{ request()->routeIs('patient.appointments') ? 'nav-link-active' : '' }}"
                                   wire:navigate
                                   @if(request()->routeIs('patient.appointments')) aria-current="page" @endif>
                                    Appointments
                                </a>
                                <a href="{{ route('patient.records') }}"
                                   class="nav-link {{ request()->routeIs('patient.records') ? 'nav-link-active' : '' }}"
                                   wire:navigate
                                   @if(request()->routeIs('patient.records')) aria-current="page" @endif>
                                    Medical Records
                                </a>
                                <a href="{{ route('patient.invoices') }}"
                                   class="nav-link {{ request()->routeIs('patient.invoices') ? 'nav-link-active' : '' }}"
                                   wire:navigate
                                   @if(request()->routeIs('patient.invoices')) aria-current="page" @endif>
                                    Invoices
                                </a>
                            </div>
                        </div>

                        {{-- Right side --}}
                        <div class="flex items-center gap-3">
                            {{-- Book Appointment CTA --}}
                            <a href="{{ route('patient.book') }}"
                               class="hidden sm:inline-flex btn-primary"
                               wire:navigate>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Book Appointment
                            </a>

                            {{-- User menu --}}
                            @php $patientPhoto = auth()->user()->patient?->photoUrl(); @endphp
                            <div class="relative" x-data="{ userOpen: false }">
                                <button @click="userOpen = !userOpen"
                                        @keydown.escape="userOpen = false"
                                        :aria-expanded="userOpen"
                                        aria-haspopup="true"
                                        aria-controls="user-menu"
                                        class="flex items-center gap-2 px-2 py-1.5 rounded-xl hover:bg-slate-100 transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500">
                                    @if($patientPhoto)
                                        <img src="{{ $patientPhoto }}" alt="{{ auth()->user()->name }}"
                                             class="w-8 h-8 rounded-full object-cover border border-cyan-200" aria-hidden="true">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-cyan-100 flex items-center justify-center text-cyan-700 font-heading font-bold text-sm border border-cyan-200" aria-hidden="true">
                                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="hidden md:block text-sm font-medium text-slate-700 max-w-[140px] truncate">{{ auth()->user()->name }}</span>
                                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-150" :class="{ 'rotate-180': userOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                <div id="user-menu"
                                     x-show="userOpen"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     @click.away="userOpen = false"
                                     @keydown.escape.window="userOpen = false"
                                     role="menu"
                                     aria-label="User menu"
                                     class="absolute right-0 top-full mt-2 w-56 bg-white rounded-2xl shadow-lg border border-slate-200 py-2 z-50"
                                     style="display: none;">
                                    {{-- User info header --}}
                                    <div class="px-4 py-3 border-b border-slate-100 mb-1 flex items-center gap-3">
                                        @if($patientPhoto)
                                            <img src="{{ $patientPhoto }}" alt="{{ auth()->user()->name }}"
                                                 class="w-10 h-10 rounded-full object-cover border border-cyan-200 flex-shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-400 to-teal-500 flex items-center justify-center text-white font-heading font-bold text-sm flex-shrink-0">
                                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-slate-900 truncate">{{ auth()->user()->name }}</p>
                                            <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</p>
                                        </div>
                                    </div>
                                    {{-- My Profile link --}}
                                    <a href="{{ route('patient.profile') }}"
                                       role="menuitem"
                                       wire:navigate
                                       class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors duration-150">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        My Profile
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                                role="menuitem"
                                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            Sign Out
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Mobile hamburger --}}
                            <button @click="mobileOpen = !mobileOpen"
                                    :aria-expanded="mobileOpen"
                                    aria-controls="mobile-nav"
                                    aria-label="Toggle mobile menu"
                                    class="md:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500">
                                <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                                <svg x-show="mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" style="display:none">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Mobile menu --}}
                <div id="mobile-nav"
                     x-show="mobileOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="md:hidden border-t border-slate-200 bg-white"
                     style="display: none;">
                    <div class="px-4 py-3 space-y-1">
                        <a href="{{ route('patient.dashboard') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('patient.dashboard') ? 'text-cyan-700 bg-cyan-50' : 'text-slate-700 hover:bg-slate-50' }}"
                           wire:navigate @click="mobileOpen = false">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Dashboard
                        </a>
                        <a href="{{ route('patient.appointments') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('patient.appointments') ? 'text-cyan-700 bg-cyan-50' : 'text-slate-700 hover:bg-slate-50' }}"
                           wire:navigate @click="mobileOpen = false">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Appointments
                        </a>
                        <a href="{{ route('patient.records') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('patient.records') ? 'text-cyan-700 bg-cyan-50' : 'text-slate-700 hover:bg-slate-50' }}"
                           wire:navigate @click="mobileOpen = false">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Medical Records
                        </a>
                        <a href="{{ route('patient.invoices') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('patient.invoices') ? 'text-cyan-700 bg-cyan-50' : 'text-slate-700 hover:bg-slate-50' }}"
                           wire:navigate @click="mobileOpen = false">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                            </svg>
                            Invoices
                        </a>
                        <div class="pt-2 border-t border-slate-100 mt-2">
                            <a href="{{ route('patient.book') }}"
                               class="flex items-center justify-center gap-2 w-full py-3 bg-cyan-600 text-white text-sm font-semibold rounded-xl hover:bg-cyan-700 transition-colors"
                               wire:navigate @click="mobileOpen = false">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Book Appointment
                            </a>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div role="status" aria-live="polite" class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                <div class="rounded-xl bg-green-50 px-4 py-3 border border-green-200 flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-green-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div role="alert" aria-live="assertive" class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                <div class="rounded-xl bg-red-50 px-4 py-3 border border-red-200 flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.834-1.964-.834-2.732 0L3.07 16.5C2.3 17.333 3.262 19 4.802 19z"/>
                    </svg>
                    <p class="text-sm text-red-800 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Main Content --}}
        <main id="main-content" role="main" class="flex-1 max-w-7xl mx-auto w-full py-8 px-4 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer role="contentinfo" class="bg-white border-t border-slate-200 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ \App\Models\SiteSetting::instance()->logoUrl() }}"
                             alt="Gonzales Dental Clinic"
                             class="h-7 w-auto object-contain">
                        <span class="text-slate-300">·</span>
                        <span class="text-sm text-slate-500">Patient Portal</span>
                    </div>
                    <p class="text-xs text-slate-400">&copy; {{ date('Y') }} Gonzales Dental Clinic All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
