<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Home' }} — {{ \App\Models\SiteSetting::instance()->clinic_name }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600;700;800&family=Noto+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-600">

    <a href="#main-content" class="skip-link">Skip to main content</a>

    @php
        $settings = \App\Models\SiteSetting::instance();
        $navLinks = [
            'home' => ['label' => 'Home', 'route' => 'home'],
            'about' => ['label' => 'About Us', 'route' => 'about'],
            'services' => ['label' => 'Services', 'route' => 'services'],
            'booking' => ['label' => 'Booking', 'route' => 'booking'],
            'contact' => ['label' => 'Contact', 'route' => 'contact'],
        ];
    @endphp

    <div class="min-h-dvh flex flex-col">

        {{-- Navigation --}}
        <header role="banner">
            <nav role="navigation" aria-label="Main navigation"
                 class="bg-white/95 backdrop-blur-sm shadow-sm border-b border-slate-200 sticky top-0 z-40"
                 x-data="{ mobileOpen: false }">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-20">

                        {{-- Brand --}}
                        <a href="{{ route('home') }}"
                           class="flex items-center gap-3 flex-shrink-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 rounded-lg"
                           wire:navigate
                           aria-label="{{ $settings->clinic_name }} home">
                            <img src="{{ $settings->logoUrl() }}"
                                 alt="{{ $settings->clinic_name }}"
                                 class="h-11 w-11 object-cover rounded-full ring-2 ring-teal-100">
                            <span class="hidden sm:block font-heading font-bold text-lg text-slate-900 leading-tight">
                                {{ $settings->clinic_name }}
                            </span>
                        </a>

                        {{-- Desktop nav --}}
                        <div class="hidden lg:flex items-center gap-1">
                            @foreach ($navLinks as $key => $link)
                                <a href="{{ route($link['route']) }}"
                                   class="px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-150 {{ request()->routeIs($link['route']) ? 'text-teal-700 bg-teal-50' : 'text-slate-600 hover:text-teal-700 hover:bg-teal-50' }}"
                                   wire:navigate
                                   @if(request()->routeIs($link['route'])) aria-current="page" @endif>
                                    {{ $link['label'] }}
                                </a>
                            @endforeach
                        </div>

                        {{-- Right side --}}
                        <div class="flex items-center gap-3">
                            <div class="hidden lg:flex items-center gap-2 pr-3 border-r border-slate-200">
                                <span class="flex items-center justify-center w-9 h-9 rounded-full bg-teal-50 text-teal-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </span>
                                <div class="leading-tight">
                                    <p class="text-xs text-slate-400">Call Us Anytime</p>
                                    <p class="text-sm font-semibold text-slate-900">{{ $settings->phone ?: '(555) 123-4567' }}</p>
                                </div>
                            </div>

                            <a href="{{ auth()->check() ? url('/') : route('login') }}"
                               class="hidden lg:inline-flex text-sm font-medium text-slate-600 hover:text-teal-700"
                               wire:navigate>
                                {{ auth()->check() ? 'My Account' : 'Sign In' }}
                            </a>

                            <a href="{{ auth()->check() ? url('/') : route('register') }}"
                               class="hidden sm:inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-teal-600 text-white text-sm font-semibold rounded-xl hover:bg-teal-700 focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2 transition-all duration-150 shadow-sm"
                               wire:navigate>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Book Appointment
                            </a>

                            {{-- Mobile hamburger --}}
                            <button @click="mobileOpen = !mobileOpen"
                                    :aria-expanded="mobileOpen"
                                    aria-controls="mobile-nav"
                                    aria-label="Toggle mobile menu"
                                    class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-500">
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
                     class="lg:hidden border-t border-slate-200 bg-white"
                     style="display: none;">
                    <div class="px-4 py-3 space-y-1">
                        @foreach ($navLinks as $key => $link)
                            <a href="{{ route($link['route']) }}"
                               class="block px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs($link['route']) ? 'text-teal-700 bg-teal-50' : 'text-slate-700 hover:bg-slate-50' }}"
                               wire:navigate @click="mobileOpen = false"
                               @if(request()->routeIs($link['route'])) aria-current="page" @endif>
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                        <div class="pt-3 mt-2 border-t border-slate-100 flex flex-col gap-2">
                            <a href="{{ auth()->check() ? url('/') : route('login') }}"
                               class="flex items-center justify-center w-full py-2.5 border border-slate-300 text-slate-700 text-sm font-medium rounded-xl hover:bg-slate-50"
                               wire:navigate @click="mobileOpen = false">
                                {{ auth()->check() ? 'My Account' : 'Sign In' }}
                            </a>
                            <a href="{{ auth()->check() ? url('/') : route('register') }}"
                               class="flex items-center justify-center gap-2 w-full py-2.5 bg-teal-600 text-white text-sm font-semibold rounded-xl hover:bg-teal-700"
                               wire:navigate @click="mobileOpen = false">
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

        {{-- Main Content --}}
        <main id="main-content" role="main" class="flex-1">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer role="contentinfo" class="bg-slate-900 text-slate-300 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">

                    {{-- Brand --}}
                    <div class="sm:col-span-2 lg:col-span-1">
                        <a href="{{ route('home') }}" class="flex items-center gap-3 mb-4" wire:navigate>
                            <img src="{{ $settings->logoUrl() }}" alt="{{ $settings->clinic_name }}" class="h-10 w-auto object-contain bg-white rounded-lg p-1">
                            <span class="font-heading font-bold text-white">{{ $settings->clinic_name }}</span>
                        </a>
                        <p class="text-sm text-slate-400 leading-relaxed">{{ $settings->tagline }}</p>
                        @if($settings->facebook_url)
                            <a href="{{ $settings->facebook_url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-slate-800 text-slate-300 hover:bg-teal-600 hover:text-white transition-colors duration-150 mt-4"
                               aria-label="Visit our Facebook page">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/>
                                </svg>
                            </a>
                        @endif
                    </div>

                    {{-- Quick Links --}}
                    <div>
                        <h3 class="font-heading font-semibold text-white mb-4">Quick Links</h3>
                        <ul class="space-y-2.5 text-sm">
                            @foreach ($navLinks as $key => $link)
                                <li>
                                    <a href="{{ route($link['route']) }}" class="hover:text-teal-400 transition-colors duration-150" wire:navigate>{{ $link['label'] }}</a>
                                </li>
                            @endforeach
                            <li>
                                <a href="{{ route('login') }}" class="hover:text-teal-400 transition-colors duration-150" wire:navigate>Patient Portal</a>
                            </li>
                        </ul>
                    </div>

                    {{-- Contact --}}
                    <div>
                        <h3 class="font-heading font-semibold text-white mb-4">Contact Us</h3>
                        <ul class="space-y-3 text-sm">
                            @if($settings->address || $settings->city)
                                <li class="flex items-start gap-2.5">
                                    <svg class="w-4 h-4 mt-0.5 text-teal-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>{{ $settings->address }}@if($settings->address && $settings->city), @endif{{ $settings->city }}</span>
                                </li>
                            @endif
                            @if($settings->phone)
                                <li class="flex items-center gap-2.5">
                                    <svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span>{{ $settings->phone }}</span>
                                </li>
                            @endif
                            @if($settings->email)
                                <li class="flex items-center gap-2.5">
                                    <svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span>{{ $settings->email }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>

                    {{-- Clinic Hours --}}
                    <div>
                        <h3 class="font-heading font-semibold text-white mb-4">Clinic Hours</h3>
                        <ul class="space-y-2.5 text-sm">
                            <li class="flex justify-between gap-4"><span>Mon – Fri</span><span class="text-slate-400">{{ $settings->hours_weekday ?: '9:00 AM – 6:00 PM' }}</span></li>
                            <li class="flex justify-between gap-4"><span>Saturday</span><span class="text-slate-400">{{ $settings->hours_saturday ?: '9:00 AM – 2:00 PM' }}</span></li>
                            <li class="flex justify-between gap-4"><span>Sunday</span><span class="text-slate-400">{{ $settings->hours_sunday ?: 'Closed' }}</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
                    <p class="text-center text-xs text-slate-500">
                        {{ $settings->footer_text ?: '© ' . date('Y') . ' ' . $settings->clinic_name . '. All rights reserved.' }}
                    </p>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
