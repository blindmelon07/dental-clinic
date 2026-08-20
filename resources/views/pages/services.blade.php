<x-layouts.guest title="Services">

    @php
        $settings = \App\Models\SiteSetting::instance();
        $categories = \App\Models\ServiceCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->with(['services' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->get();
    @endphp

    {{-- Page Header --}}
    <section class="bg-gradient-to-br from-blue-50 via-blue-50 to-white py-14 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="section-label">What We Offer</span>
            <h1 class="mt-4 font-heading text-4xl sm:text-5xl font-extrabold text-slate-900">Our Services</h1>
            <p class="mt-4 max-w-2xl mx-auto text-slate-600 text-lg">
                Comprehensive dental care for every stage of life &mdash; from routine checkups to advanced restorative
                and cosmetic treatments, all delivered with a gentle, patient-first touch.
            </p>
        </div>
    </section>

    {{-- Services Grid --}}
    <section class="py-16 sm:py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @forelse ($categories as $category)
                <div class="mb-14">
                    {{-- Category heading --}}
                    <div class="flex items-center gap-3 mb-6">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-heading text-2xl font-bold text-slate-900">{{ $category->name }}</h2>
                            @if($category->description)
                                <p class="text-sm text-slate-500 mt-0.5">{{ $category->description }}</p>
                            @endif
                        </div>
                    </div>

                    @if($category->services->isNotEmpty())
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($category->services as $service)
                                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col hover:shadow-md hover:-translate-y-0.5 transition-all duration-200" data-sr style="transition-delay: {{ ($loop->index % 3) * 0.12 }}s">
                                    {{-- Icon --}}
                                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 mb-5 text-2xl">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </div>

                                    {{-- Name --}}
                                    <h3 class="font-heading text-xl font-bold text-blue-800">{{ $service->name }}</h3>

                                    {{-- Description --}}
                                    @if($service->description)
                                        <p class="mt-2 text-sm text-slate-500 leading-relaxed flex-1">{{ $service->description }}</p>
                                    @else
                                        <p class="mt-2 text-sm text-slate-400 leading-relaxed flex-1 italic">Professional dental care performed by our experienced team.</p>
                                    @endif

                                    {{-- Duration --}}
                                    <div class="mt-5 pt-4 border-t border-slate-100">
                                        @if($service->duration_minutes)
                                            <p class="mt-0.5 text-xs text-slate-400 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ $service->duration_minutes }} mins
                                            </p>
                                        @endif
                                    </div>

                                    {{-- CTA --}}
                                    <a href="{{ auth()->check() ? url('/') : route('register') }}"
                                       class="mt-4 inline-flex items-center justify-center w-full px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-semibold rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-sm shadow-blue-200 transition-all duration-150"
                                       wire:navigate>
                                        Book Appointment
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">Details for this category are coming soon.</p>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm text-center py-16">
                    <p class="text-slate-500">Our services list is being updated. Please contact us for current offerings.</p>
                    <a href="{{ route('contact') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-800" wire:navigate>
                        Contact Us
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            @endforelse

        </div>
    </section>

    {{-- Sidebar info as a bottom strip --}}
    <section class="py-12 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-blue-50 rounded-2xl p-6" data-sr style="transition-delay: 0s">
                    <h3 class="font-heading text-lg font-bold text-slate-900">Why Patients Trust Us</h3>
                    <ul class="mt-4 space-y-2.5">
                        <li class="flex items-start gap-2.5 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Licensed &amp; experienced dental professionals
                        </li>
                        <li class="flex items-start gap-2.5 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Modern, sanitized equipment and facilities
                        </li>
                        <li class="flex items-start gap-2.5 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Transparent pricing, no hidden fees
                        </li>
                        <li class="flex items-start gap-2.5 text-sm text-slate-700">
                            <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Gentle, patient-first approach to every visit
                        </li>
                    </ul>
                </div>
                <div class="bg-slate-50 rounded-2xl p-6 flex flex-col justify-between" data-sr style="transition-delay: 0.12s">
                    <div>
                        <h3 class="font-heading text-lg font-bold text-slate-900">Need Help Choosing?</h3>
                        <p class="mt-2 text-sm text-slate-600">Not sure which treatment is right for you? Our team is happy to walk you through your options.</p>
                    </div>
                    <a href="{{ route('contact') }}"
                       class="mt-5 inline-flex items-center justify-center gap-2 w-full px-5 py-3 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition-all duration-150"
                       wire:navigate>
                        Contact Our Team
                    </a>
                </div>
                <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl p-6 flex flex-col justify-between" data-sr style="transition-delay: 0.24s">
                    <div>
                        <h3 class="font-heading text-lg font-bold text-white">Ready to Book?</h3>
                        <p class="mt-2 text-sm text-blue-100">Schedule your appointment online in just a few minutes.</p>
                    </div>
                    <a href="{{ auth()->check() ? url('/') : route('register') }}"
                       class="mt-5 inline-flex items-center justify-center gap-2 w-full px-5 py-3 bg-white text-blue-800 text-sm font-semibold rounded-xl hover:bg-blue-50 transition-all duration-150"
                       wire:navigate>
                        Book Appointment
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Trust badges strip --}}
    <section class="py-12 bg-blue-50/60 border-t border-blue-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-8 text-center">
                <div data-sr style="transition-delay: 0s">
                    <p class="font-heading text-3xl font-extrabold text-blue-700">{{ $settings->stat_years ?: '15+' }}</p>
                    <p class="mt-1 text-sm text-slate-500">Years Experience</p>
                </div>
                <div data-sr style="transition-delay: 0.12s">
                    <p class="font-heading text-3xl font-extrabold text-blue-700">{{ $settings->stat_patients ?: '10k+' }}</p>
                    <p class="mt-1 text-sm text-slate-500">Happy Patients</p>
                </div>
                <div data-sr style="transition-delay: 0.24s">
                    <p class="font-heading text-3xl font-extrabold text-blue-700">{{ $settings->stat_satisfaction ?: '98%' }}</p>
                    <p class="mt-1 text-sm text-slate-500">Satisfaction Rate</p>
                </div>
                <div data-sr style="transition-delay: 0.36s">
                    <p class="font-heading text-3xl font-extrabold text-blue-700">{{ $settings->stat_emergency ?: '24/7' }}</p>
                    <p class="mt-1 text-sm text-slate-500">Emergency Care</p>
                </div>
            </div>
        </div>
    </section>

</x-layouts.guest>
