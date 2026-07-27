<x-layouts.guest title="About Us">

    @php
        $settings = \App\Models\SiteSetting::instance();
        $dentists = \App\Models\Dentist::where('is_active', true)
            ->with('user')
            ->take(4)
            ->get();
    @endphp

    {{-- Page Header --}}
    <section class="bg-gradient-to-br from-blue-50 via-blue-50 to-white py-14 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="section-label">Get To Know Us</span>
            <h1 class="mt-4 font-heading text-4xl sm:text-5xl font-extrabold text-slate-900">About {{ $settings->clinic_name }}</h1>
            <p class="mt-4 max-w-2xl mx-auto text-slate-600 text-lg">
                {{ $settings->tagline }}. Here's a closer look at who we are and why our patients keep coming back.
            </p>
        </div>
    </section>

    {{-- Story --}}
    <section class="py-16 sm:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="relative">
                    <div class="aspect-[4/3] rounded-3xl overflow-hidden shadow-xl">
                        <img src="{{ $settings->about_story_image ? asset('storage/' . $settings->about_story_image) : 'https://picsum.photos/seed/dental-clinic-interior/800/600' }}" alt="Inside our dental clinic" class="w-full h-full object-cover" loading="lazy">
                    </div>
                </div>
                <div>
                    <span class="section-label">Our Story</span>
                    <h2 class="mt-4 font-heading text-3xl sm:text-4xl font-bold text-slate-900">
                        {{ $settings->about_story_heading ?: 'Care With Compassion' }}
                    </h2>
                    <p class="mt-6 text-slate-600 leading-relaxed whitespace-pre-line">
                        {{ $settings->about_story_body ?: $settings->clinic_name . ' was founded with a simple goal: to make quality dental care approachable, comfortable, and stress-free for every patient who walks through our doors. What started as a small practice has grown into a trusted community clinic, serving thousands of families across ' . ($settings->city ?: 'our community') . '.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Feature Cards --}}
    <section class="py-16 sm:py-24 bg-blue-50/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto text-center">
                <span class="section-label">What Sets Us Apart</span>
                <h2 class="mt-4 font-heading text-3xl sm:text-4xl font-bold text-slate-900">Built Around Our Patients</h2>
            </div>

            <div class="mt-12 grid sm:grid-cols-3 gap-6">
                <div class="card text-center">
                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-50 text-blue-700 mx-auto mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 3a4 4 0 10-8 0"/>
                        </svg>
                    </div>
                    <h3 class="font-heading text-lg font-bold text-slate-900">Expert Team</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Our licensed dentists and hygienists bring years of combined experience across every area of dental care.
                    </p>
                </div>
                <div class="card text-center">
                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-50 text-blue-700 mx-auto mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-heading text-lg font-bold text-slate-900">Advanced Technology</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        We invest in modern equipment and digital tools to make every procedure safer, faster, and more comfortable.
                    </p>
                </div>
                <div class="card text-center">
                    <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-50 text-blue-700 mx-auto mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-heading text-lg font-bold text-slate-900">Patient-First Care</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Every treatment plan is personalized &mdash; we take the time to listen, explain, and ensure you feel comfortable.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Our Journey Timeline --}}
    <section class="py-16 sm:py-24 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto text-center">
                <span class="section-label">Our Journey</span>
                <h2 class="mt-4 font-heading text-3xl sm:text-4xl font-bold text-slate-900">Milestones Along the Way</h2>
            </div>

            <div class="mt-12 space-y-8">
                <div class="flex gap-6">
                    <div class="flex flex-col items-center">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-700 text-white font-heading font-bold text-sm flex-shrink-0">01</div>
                        <div class="w-px flex-1 bg-blue-100 mt-2"></div>
                    </div>
                    <div class="pb-8">
                        <h3 class="font-heading text-lg font-bold text-slate-900">{{ $settings->milestone_1_title ?: 'Our Clinic Opens' }}</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                            {{ $settings->milestone_1_body ?: 'We opened our doors with a small team and a big mission — to provide friendly, honest dental care to our community.' }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-6">
                    <div class="flex flex-col items-center">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-700 text-white font-heading font-bold text-sm flex-shrink-0">02</div>
                        <div class="w-px flex-1 bg-blue-100 mt-2"></div>
                    </div>
                    <div class="pb-8">
                        <h3 class="font-heading text-lg font-bold text-slate-900">{{ $settings->milestone_2_title ?: 'Expanding Our Services' }}</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                            {{ $settings->milestone_2_body ?: 'As our patient family grew, so did our services — adding cosmetic, restorative, and pediatric dentistry to our offerings.' }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-6">
                    <div class="flex flex-col items-center">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-700 text-white font-heading font-bold text-sm flex-shrink-0">03</div>
                        <div class="w-px flex-1 bg-blue-100 mt-2"></div>
                    </div>
                    <div class="pb-8">
                        <h3 class="font-heading text-lg font-bold text-slate-900">{{ $settings->milestone_3_title ?: 'Going Digital' }}</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                            {{ $settings->milestone_3_body ?: 'We introduced online booking and digital patient records to make care more convenient and accessible than ever.' }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-6">
                    <div class="flex flex-col items-center">
                        <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-700 text-white font-heading font-bold text-sm flex-shrink-0">04</div>
                    </div>
                    <div>
                        <h3 class="font-heading text-lg font-bold text-slate-900">{{ $settings->milestone_4_title ?: 'Serving You Today' }}</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                            {{ $settings->milestone_4_body ?: 'Today, we continue to grow — guided by the same values we started with: compassion, quality, and trust.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Team --}}
    @if($dentists->isNotEmpty())
        <section class="py-16 sm:py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl mx-auto text-center">
                    <span class="section-label">Meet Our Team</span>
                    <h2 class="mt-4 font-heading text-3xl sm:text-4xl font-bold text-slate-900">Dedicated Dental Professionals</h2>
                    <p class="mt-4 text-slate-500 text-base">Our experienced dentists are committed to providing gentle, high-quality care for every patient.</p>
                </div>

                <div class="mt-14 grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($dentists as $dentist)
                        @php
                            // Strip "Dr." prefix for display and initials
                            $displayName = preg_replace('/^Dr\.\s*/i', '', $dentist->full_name);
                            $nameParts   = explode(' ', $displayName);
                            $initials    = collect($nameParts)->filter()->take(2)->map(fn($p) => strtoupper($p[0]))->implode('');

                            // Cycle through a set of teal/cyan gradient combos
                            $gradients = [
                                'from-blue-600 to-blue-600',
                                'from-blue-600 to-blue-500',
                                'from-blue-700 to-blue-500',
                            ];
                            $gradient = $gradients[$loop->index % count($gradients)];
                        @endphp

                        <div class="group bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col">

                            {{-- Avatar header --}}
                            <div class="bg-gradient-to-br from-blue-50 to-blue-50 px-8 pt-10 pb-6 flex flex-col items-center">
                                {{-- Avatar circle --}}
                                @if($dentist->user->avatar)
                                    <div class="w-28 h-28 rounded-full overflow-hidden ring-4 ring-white shadow-lg mb-4">
                                        <img src="{{ asset('storage/' . $dentist->user->avatar) }}"
                                             alt="Photo of {{ $displayName }}"
                                             class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="w-28 h-28 rounded-full bg-gradient-to-br {{ $gradient }} flex items-center justify-center ring-4 ring-white shadow-lg mb-4">
                                        <span class="text-3xl font-bold text-white tracking-wide">{{ $initials }}</span>
                                    </div>
                                @endif

                                {{-- Name & specialization --}}
                                <h3 class="font-heading text-xl font-bold text-slate-900 text-center leading-tight">{{ $displayName }}</h3>
                                <p class="mt-1.5 text-sm font-semibold text-blue-700 text-center">{{ $dentist->specialization ?: 'General Dentistry' }}</p>
                            </div>

                            {{-- Card body --}}
                            <div class="px-8 py-6 flex flex-col flex-1">
                                @if($dentist->bio)
                                    <p class="text-sm text-slate-500 leading-relaxed flex-1">
                                        {{ \Illuminate\Support\Str::limit($dentist->bio, 130) }}
                                    </p>
                                @else
                                    <p class="text-sm text-slate-400 italic flex-1">Licensed dental professional dedicated to patient-first care.</p>
                                @endif

                                {{-- Fee + book button --}}
                                <div class="mt-5 pt-5 border-t border-slate-100 flex items-center justify-between gap-3">
                                    @if($dentist->consultation_fee > 0)
                                        <div>
                                            <p class="text-xs text-slate-400 leading-none">Consultation</p>
                                            <p class="mt-0.5 font-heading font-bold text-slate-800 text-sm">₱{{ number_format((float) $dentist->consultation_fee, 0) }}</p>
                                        </div>
                                    @else
                                        <div></div>
                                    @endif
                                    <a href="{{ auth()->check() ? url('/') : route('register') }}"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-700 text-white text-xs font-semibold rounded-xl hover:bg-blue-800 transition-all duration-150 shadow-sm shadow-blue-700/20"
                                       wire:navigate>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Book a Visit
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Testimonial --}}
    <section class="py-16 sm:py-20 bg-slate-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <svg class="w-10 h-10 text-blue-400 mx-auto" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M9.983 3v7.391c0 5.704-3.731 9.57-8.983 10.609l-.995-2.151c2.432-.917 3.995-3.638 3.995-5.849h-4v-10h9.983zm14.017 0v7.391c0 5.704-3.748 9.571-9 10.609l-.996-2.151c2.433-.917 3.996-3.638 3.996-5.849h-3.983v-10h9.983z"/>
            </svg>
            <p class="mt-6 font-heading text-xl sm:text-2xl font-medium text-white leading-relaxed">
                "{{ $settings->about_testimonial_quote ?: 'From the moment you walk in, you can tell this clinic genuinely cares. The staff is welcoming, the office is immaculate, and every visit feels personal.' }}"
            </p>
            <div class="mt-6">
                <p class="font-semibold text-white">{{ $settings->about_testimonial_author ?: 'James Rivera' }}</p>
                <p class="text-sm text-slate-400">{{ $settings->about_testimonial_since ?: 'Patient since 2019' }}</p>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-16 sm:py-20 bg-gradient-to-r from-blue-700 to-blue-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="font-heading text-3xl sm:text-4xl font-bold text-white">Come See Us in Person</h2>
            <p class="mt-4 text-blue-50 text-lg">
                We'd love to welcome you to {{ $settings->clinic_name }}. Schedule your visit today.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="{{ auth()->check() ? url('/') : route('register') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-white text-blue-800 text-sm font-semibold rounded-xl hover:bg-blue-50 shadow-lg transition-all duration-150"
                   wire:navigate>
                    Book Appointment
                </a>
            </div>
        </div>
    </section>

</x-layouts.guest>
