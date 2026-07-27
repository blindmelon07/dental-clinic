<x-layouts.guest title="Contact Us">

    @php
        $settings = \App\Models\SiteSetting::instance();
    @endphp

    {{-- Page Header --}}
    <section class="bg-gradient-to-br from-blue-50 via-blue-50 to-white py-14 sm:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="section-label">Get In Touch</span>
            <h1 class="mt-4 font-heading text-4xl sm:text-5xl font-extrabold text-slate-900">Contact Us</h1>
            <p class="mt-4 max-w-2xl mx-auto text-slate-600 text-lg">
                Have a question or need to schedule a visit? Reach out and our team will get back to you as soon as possible.
            </p>
        </div>
    </section>

    {{-- Contact Info Cards --}}
    <section class="py-16 sm:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="card text-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 text-blue-700 mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <h3 class="font-heading font-bold text-slate-900">Phone</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $settings->phone ?: 'Coming soon' }}</p>
                </div>
                <div class="card text-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 text-blue-700 mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-heading font-bold text-slate-900">Email</h3>
                    <p class="mt-2 text-sm text-slate-600 break-all">{{ $settings->email ?: 'Coming soon' }}</p>
                </div>
                <div class="card text-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 text-blue-700 mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-heading font-bold text-slate-900">Location</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ $settings->address ?: 'Address coming soon' }}@if($settings->address && $settings->city), @endif{{ $settings->city }}
                    </p>
                </div>
                <div class="card text-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 text-blue-700 mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-heading font-bold text-slate-900">Office Hours</h3>
                    <p class="mt-2 text-sm text-slate-600">Mon–Fri: {{ $settings->hours_weekday ?: '9:00 AM – 6:00 PM' }}</p>
                    <p class="text-sm text-slate-600">Sat: {{ $settings->hours_saturday ?: '9:00 AM – 2:00 PM' }}</p>
                    <p class="text-sm text-slate-600">Sun: {{ $settings->hours_sunday ?: 'Closed' }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Form + Map --}}
    <section class="py-16 sm:py-24 bg-blue-50/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12">

                {{-- Contact Form --}}
                <div class="card">
                    <h2 class="font-heading text-2xl font-bold text-slate-900">Send Us a Message</h2>
                    <p class="mt-2 text-sm text-slate-600">Fill out the form below and we'll respond as soon as we can.</p>

                    <form method="POST" action="{{ route('contact.send') }}" class="mt-6 space-y-5">
                        @csrf

                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Full Name</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                       class="input-field" placeholder="Your name">
                                @error('name')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                       class="input-field" placeholder="you@example.com">
                                @error('email')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-slate-700 mb-1.5">Phone Number <span class="text-slate-400">(optional)</span></label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                       class="input-field" placeholder="(000) 000-0000">
                                @error('phone')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="subject" class="block text-sm font-medium text-slate-700 mb-1.5">Subject</label>
                                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required
                                       class="input-field" placeholder="How can we help?">
                                @error('subject')
                                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-slate-700 mb-1.5">Message</label>
                            <textarea id="message" name="message" rows="5" required
                                      class="input-field" placeholder="Write your message here...">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 w-full px-6 py-3.5 bg-blue-700 text-white text-sm font-semibold rounded-xl hover:bg-blue-800 shadow-lg shadow-blue-700/20 transition-all duration-150">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Send Message
                        </button>
                    </form>
                </div>

                {{-- Location / Map --}}
                <div class="space-y-6">
                    <div class="aspect-[4/3] rounded-3xl overflow-hidden shadow-xl">
                        <img src="{{ $settings->contact_location_image ? asset('storage/' . $settings->contact_location_image) : 'https://picsum.photos/seed/dental-clinic-location/800/600' }}" alt="Our clinic location" class="w-full h-full object-cover" loading="lazy">
                    </div>
                    <div class="card">
                        <h3 class="font-heading font-bold text-slate-900 mb-3">Visit Our Clinic</h3>
                        <div class="space-y-3 text-sm">
                            @if($settings->address || $settings->city)
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-700 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-slate-700">{{ $settings->address }}@if($settings->address && $settings->city), @endif{{ $settings->city }}</span>
                                </div>
                            @endif
                            @if($settings->phone)
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-blue-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span class="text-slate-700">{{ $settings->phone }}</span>
                                </div>
                            @endif
                            @if($settings->email)
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 text-blue-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-slate-700">{{ $settings->email }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.guest>
