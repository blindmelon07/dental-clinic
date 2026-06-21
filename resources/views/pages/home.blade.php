<x-layouts.guest title="Home">

    @php
        $settings = \App\Models\SiteSetting::instance();

        // Build hero gallery — use uploaded images when set, otherwise fall back to placeholders
        $heroSlides = [
            ['path' => $settings->hero_image_1, 'alt' => 'Smiling patient at the dental clinic'],
            ['path' => $settings->hero_image_2, 'alt' => 'Modern dental clinic interior'],
            ['path' => $settings->hero_image_3, 'alt' => 'Dental care professionals at work'],
            ['path' => $settings->hero_image_4, 'alt' => 'Happy patient after treatment'],
        ];
        $heroImages = collect($heroSlides)->map(fn($slide, $i) => [
            'src' => $slide['path'] ? asset('storage/' . $slide['path']) : 'https://picsum.photos/seed/dental-' . ($i + 1) . '/800/1000',
            'alt' => $slide['alt'],
        ])->values()->all();

        $categories = \App\Models\ServiceCategory::where('is_active', true)
            ->withCount(['services' => fn($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->take(6)
            ->get();
    @endphp

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-blue-50 via-blue-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="section-label">Welcome to {{ $settings->clinic_name }}</span>
                    <h1 class="mt-4 font-heading text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 leading-tight">
                        {{ $settings->hero_heading ?: 'Healthy Smiles' }}<br>
                        <span class="text-blue-700">{{ $settings->hero_subheading ?: 'Start Here' }}</span>
                    </h1>
                    <p class="mt-6 text-lg text-slate-600 max-w-xl">
                        {{ $settings->hero_description ?: $settings->tagline . '. From routine cleanings to advanced cosmetic procedures, our experienced team is dedicated to giving you a smile you\'ll love.' }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="{{ auth()->check() ? url('/') : route('register') }}"
                           class="inline-flex items-center justify-center gap-2 px-7 py-3.5 bg-blue-700 text-white text-sm font-semibold rounded-xl hover:bg-blue-800 shadow-lg shadow-blue-700/20 transition-all duration-150"
                           wire:navigate>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Book Appointment
                        </a>
                        <a href="{{ route('services') }}"
                           class="inline-flex items-center justify-center gap-2 px-7 py-3.5 bg-white text-slate-700 text-sm font-semibold rounded-xl border border-slate-200 hover:border-blue-300 hover:text-blue-800 transition-all duration-150"
                           wire:navigate>
                            Our Services
                        </a>
                    </div>

                    {{-- Trust badges --}}
                    <div class="mt-12 grid grid-cols-2 sm:grid-cols-4 gap-6">
                        <div>
                            <p class="font-heading text-3xl font-extrabold text-blue-700">{{ $settings->stat_years ?: '15+' }}</p>
                            <p class="mt-1 text-sm text-slate-500">Years Experience</p>
                        </div>
                        <div>
                            <p class="font-heading text-3xl font-extrabold text-blue-700">{{ $settings->stat_patients ?: '10k+' }}</p>
                            <p class="mt-1 text-sm text-slate-500">Happy Patients</p>
                        </div>
                        <div>
                            <p class="font-heading text-3xl font-extrabold text-blue-700">{{ $settings->stat_satisfaction ?: '98%' }}</p>
                            <p class="mt-1 text-sm text-slate-500">Satisfaction Rate</p>
                        </div>
                        <div>
                            <p class="font-heading text-3xl font-extrabold text-blue-700">{{ $settings->stat_emergency ?: '24/7' }}</p>
                            <p class="mt-1 text-sm text-slate-500">Emergency Care</p>
                        </div>
                    </div>
                </div>

                {{-- Hero gallery Alpine component — defined in <script> to avoid JSON quote conflict in x-data attribute --}}
                <script>
                    document.addEventListener('alpine:init', () => {
                        Alpine.data('heroGallery', () => ({
                            current: 0,
                            images: @json($heroImages),
                            autoplay: null,
                            init() { this.start(); },
                            start() {
                                this.autoplay = setInterval(() => {
                                    this.current = (this.current + 1) % this.images.length;
                                }, 3500);
                            },
                            stop() { clearInterval(this.autoplay); },
                            go(i)  { this.stop(); this.current = i; this.start(); }
                        }));
                    });
                </script>

                <div class="relative"
                     x-data="heroGallery"
                     @mouseenter="stop()"
                     @mouseleave="start()">

                    {{-- Gallery frame --}}
                    <div class="aspect-[4/5] rounded-3xl overflow-hidden shadow-2xl relative">
                        <template x-for="(img, i) in images" :key="i">
                            <img :src="img.src"
                                 :alt="img.alt"
                                 x-show="current === i"
                                 x-transition:enter="transition ease-in-out duration-700"
                                 x-transition:enter-start="opacity-0 scale-105"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in-out duration-700"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute inset-0 w-full h-full object-cover"
                                 style="display:none;">
                        </template>

                        {{-- Prev / Next arrows --}}
                        <button @click="go((current - 1 + images.length) % images.length)"
                                class="absolute left-3 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm text-slate-700 flex items-center justify-center shadow hover:bg-white transition"
                                aria-label="Previous image">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button @click="go((current + 1) % images.length)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white/80 backdrop-blur-sm text-slate-700 flex items-center justify-center shadow hover:bg-white transition"
                                aria-label="Next image">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>

                        {{-- Dot indicators --}}
                        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-10 flex items-center gap-2">
                            <template x-for="(img, i) in images" :key="i">
                                <button @click="go(i)"
                                        :class="current === i ? 'w-6 bg-white' : 'w-2 bg-white/50 hover:bg-white/80'"
                                        class="h-2 rounded-full transition-all duration-300"
                                        :aria-label="'Go to image ' + (i + 1)">
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Certified & Trusted badge --}}
                    <div class="absolute -bottom-6 -left-6 bg-white rounded-2xl shadow-xl p-5 hidden sm:flex items-center gap-4 max-w-xs">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-50 text-blue-700 flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-heading font-bold text-slate-900 text-sm">Certified &amp; Trusted</p>
                            <p class="text-xs text-slate-500">Licensed dental professionals</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Services Preview --}}
    <section class="py-16 sm:py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto text-center">
                <span class="section-label">What We Offer</span>
                <h2 class="mt-4 font-heading text-3xl sm:text-4xl font-bold text-slate-900">Comprehensive Dental Care</h2>
                <p class="mt-4 text-slate-600">
                    From preventive checkups to advanced restorative and cosmetic treatments, we provide complete dental care for the whole family.
                </p>
            </div>

            <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($categories as $category)
                    @php $count = $category->services_count; @endphp
                    <div class="group bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">

                        {{-- Gradient header --}}
                        <div class="relative bg-gradient-to-br from-blue-600 to-blue-800 p-8 flex flex-col items-center justify-center">
                            {{-- Icon --}}
                            <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm text-white group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>

                            {{-- Service count badge --}}
                            @if($count > 0)
                                <span class="absolute top-3 right-3 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-white/25 text-white backdrop-blur-sm">
                                    {{ $count }} {{ Str::plural('service', $count) }}
                                </span>
                            @endif
                        </div>

                        {{-- Card body --}}
                        <div class="p-6 flex flex-col flex-1">
                            <h3 class="font-heading text-lg font-bold text-slate-900">{{ $category->name }}</h3>
                            <p class="mt-2 text-sm text-slate-500 leading-relaxed flex-1">
                                {{ $category->description ?: 'Quality dental care tailored to your needs, performed by our experienced team using modern techniques.' }}
                            </p>
                            <a href="{{ route('services') }}"
                               class="mt-5 inline-flex items-center justify-between w-full px-4 py-2.5 rounded-xl bg-blue-50 text-blue-800 text-sm font-semibold hover:bg-blue-700 hover:text-white transition-all duration-200 group/btn"
                               wire:navigate>
                                <span>Learn More</span>
                                <svg class="w-4 h-4 group-hover/btn:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center text-slate-500 py-12">
                        Service information is being updated. Please check back soon.
                    </div>
                @endforelse
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('services') }}"
                   class="inline-flex items-center justify-center gap-2 px-7 py-3.5 bg-blue-700 text-white text-sm font-semibold rounded-xl hover:bg-blue-800 shadow-lg shadow-blue-700/20 transition-all duration-150"
                   wire:navigate>
                    View All Services
                </a>
            </div>
        </div>
    </section>

    {{-- About Teaser --}}
    <section class="py-16 sm:py-24 bg-blue-50/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="relative order-2 lg:order-1">
                    <div class="aspect-[4/3] rounded-3xl overflow-hidden shadow-xl">
                        <img src="https://picsum.photos/seed/dental-team/800/600" alt="Dental team in our clinic" class="w-full h-full object-cover" loading="lazy">
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <span class="section-label">Why Choose Us</span>
                    <h2 class="mt-4 font-heading text-3xl sm:text-4xl font-bold text-slate-900">
                        Comfortable Care, Confident You
                    </h2>
                    <p class="mt-6 text-slate-600 leading-relaxed">
                        At {{ $settings->clinic_name }}, we believe a visit to the dentist should never be stressful. Our warm,
                        modern clinic combines advanced technology with a gentle, patient-first approach so you can feel
                        relaxed and confident in your care &mdash; every step of the way.
                    </p>
                    <ul class="mt-6 space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-700 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-slate-700">Experienced, friendly dental professionals</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-700 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-slate-700">Modern equipment for safe, precise treatment</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-700 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-slate-700">Personalized treatment plans for every patient</span>
                        </li>
                    </ul>
                    <a href="{{ route('about') }}"
                       class="mt-8 inline-flex items-center justify-center gap-2 px-7 py-3.5 bg-slate-900 text-white text-sm font-semibold rounded-xl hover:bg-slate-800 transition-all duration-150"
                       wire:navigate>
                        More About Us
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Testimonial Banner --}}
    <section class="py-16 sm:py-20 bg-slate-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <svg class="w-10 h-10 text-blue-400 mx-auto" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M9.983 3v7.391c0 5.704-3.731 9.57-8.983 10.609l-.995-2.151c2.432-.917 3.995-3.638 3.995-5.849h-4v-10h9.983zm14.017 0v7.391c0 5.704-3.748 9.571-9 10.609l-.996-2.151c2.433-.917 3.996-3.638 3.996-5.849h-3.983v-10h9.983z"/>
            </svg>
            <p class="mt-6 font-heading text-xl sm:text-2xl font-medium text-white leading-relaxed">
                "{{ $settings->testimonial_quote ?: 'The entire team made me feel completely at ease. They explained everything clearly and the office is spotless and modern. I actually look forward to my checkups now!' }}"
            </p>
            <div class="mt-6">
                <p class="font-semibold text-white">{{ $settings->testimonial_author ?: 'Maria Santos' }}</p>
                <p class="text-sm text-slate-400">{{ $settings->testimonial_since ?: 'Patient since 2021' }}</p>
            </div>
        </div>
    </section>

    {{-- CTA Banner --}}
    <section class="py-16 sm:py-20 bg-gradient-to-r from-blue-700 to-blue-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="font-heading text-3xl sm:text-4xl font-bold text-white">Ready to Get Started?</h2>
            <p class="mt-4 text-blue-50 text-lg">
                Book your appointment today and take the first step toward a healthier, brighter smile.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="{{ auth()->check() ? url('/') : route('register') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-white text-blue-800 text-sm font-semibold rounded-xl hover:bg-blue-50 shadow-lg transition-all duration-150"
                   wire:navigate>
                    Book Appointment
                </a>
                <a href="{{ route('contact') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-blue-800/30 text-white text-sm font-semibold rounded-xl border border-white/30 hover:bg-blue-800/50 transition-all duration-150"
                   wire:navigate>
                    Contact Us
                </a>
            </div>
        </div>
    </section>

</x-layouts.guest>
