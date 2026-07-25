<x-layouts.guest title="Booking">

    @php
        $settings  = \App\Models\SiteSetting::instance();
        $dentists  = \App\Models\Dentist::where('is_active', true)->with('user')->get();
        $services  = \App\Models\Service::where('is_active', true)
                        ->with('category')
                        ->orderBy('sort_order')
                        ->get();
        $firstService = $services->first();
        $firstDentist = $dentists->first();

        $timeSlots = [];
        for ($h = 7; $h < 17; $h++) {
            foreach ([0, 30] as $m) {
                $time24 = sprintf('%02d:%02d', $h, $m);
                $period = $h < 12 ? 'AM' : 'PM';
                $h12    = $h > 12 ? $h - 12 : ($h === 0 ? 12 : $h);
                $timeSlots[$time24] = sprintf('%d:%02d %s', $h12, $m, $period);
            }
        }
    @endphp

    <script>
        function bookingForm() {
            return {
                serviceId: '{{ $firstService?->id }}',
                dentistId: '{{ $firstDentist?->id }}',
                date: '',
                time: '',
                services: @json($services->keyBy('id')->map(fn($s) => ['name' => $s->name, 'price' => (float) $s->price])),
                dentists: @json($dentists->keyBy('id')->map(fn($d) => ['name' => $d->full_name, 'specialization' => $d->specialization])),
                timeSlots: @json($timeSlots),
                get serviceName() {
                    return this.serviceId && this.services[this.serviceId]
                        ? this.services[this.serviceId].name
                        : 'Select a service';
                },
                get servicePrice() {
                    const p = this.serviceId && this.services[this.serviceId]
                        ? this.services[this.serviceId].price : 0;
                    return p > 0 ? p : null;
                },
                get dentistName() {
                    return this.dentistId && this.dentists[this.dentistId]
                        ? this.dentists[this.dentistId].name
                        : 'Select a doctor';
                },
                get dentistSpec() {
                    return this.dentistId && this.dentists[this.dentistId]
                        ? this.dentists[this.dentistId].specialization
                        : null;
                },
                get formattedDate() {
                    if (!this.date) return 'Your preferred date';
                    const d = new Date(this.date + 'T00:00:00');
                    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                },
                get formattedTime() {
                    return this.time && this.timeSlots[this.time]
                        ? this.timeSlots[this.time]
                        : 'Your preferred time';
                },
                formatPrice(p) {
                    return '₱' + Number(p).toLocaleString('en-PH', { minimumFractionDigits: 0 });
                }
            };
        }
    </script>

    {{-- Page Header --}}
    <section class="bg-gradient-to-br from-blue-50 via-blue-50 to-white py-10 sm:py-14 border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="font-heading text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight">
                Book Your Visit<br>
                <span class="text-blue-700">In Minutes</span>
            </h1>
            <p class="mt-4 text-slate-500 text-base max-w-xl mx-auto">
                Simple. Fast. Convenient. Schedule your appointment in just a few easy steps.
            </p>
        </div>
    </section>

    {{-- Main Booking Layout --}}
    <section class="py-12 sm:py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-3 gap-8 items-start" x-data="bookingForm()">

                {{-- Left: Form --}}
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">

                    {{-- Step Progress Bar --}}
                    <div class="flex items-center justify-between mb-8 relative">
                        <div class="absolute top-4 left-0 right-0 h-0.5 bg-slate-200 z-0"></div>
                        @foreach ([['label' => 'Service', 'active' => true], ['label' => 'Doctor', 'active' => false], ['label' => 'Date & Time', 'active' => false], ['label' => 'Your Info', 'active' => false], ['label' => 'Confirm', 'active' => false]] as $i => $step)
                            <div class="relative z-10 flex flex-col items-center gap-1.5">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold
                                    {{ $step['active'] ? 'bg-blue-700 text-white shadow-md shadow-blue-200' : 'bg-white border-2 border-slate-200 text-slate-400' }}">
                                    {{ $i + 1 }}
                                </div>
                                <span class="text-xs font-medium {{ $step['active'] ? 'text-blue-700' : 'text-slate-400' }} hidden sm:block whitespace-nowrap">
                                    {{ $step['label'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Step 1: Select Service --}}
                    <div class="mb-7">
                        <h2 class="font-heading text-base font-bold text-slate-800 mb-3">1. Select Service</h2>

                        @if($services->isNotEmpty())
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <select name="service_id" x-model="serviceId"
                                        class="w-full pl-9 pr-10 py-3 border border-slate-200 rounded-xl bg-slate-50 text-sm text-slate-700 appearance-none focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition-colors">
                                    <option value="">— Choose a service —</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}">
                                            {{ $service->name }}
                                            @if($service->price > 0) — ₱{{ number_format((float)$service->price, 0) }}@endif
                                            @if($service->duration_minutes) ({{ $service->duration_minutes }} mins)@endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-slate-400 italic px-4 py-3 border border-slate-200 rounded-xl bg-slate-50">
                                No services available at the moment.
                            </p>
                        @endif
                    </div>

                    {{-- Step 2: Choose Doctor --}}
                    <div class="mb-7">
                        <h2 class="font-heading text-base font-bold text-slate-800 mb-3">2. Choose Doctor</h2>

                        @forelse ($dentists as $dentist)
                            <label class="flex items-center gap-4 px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 hover:border-blue-400 transition-colors cursor-pointer mb-3 last:mb-0">
                                {{-- Avatar --}}
                                <div class="w-12 h-12 rounded-full flex-shrink-0 overflow-hidden bg-blue-50">
                                    @if($dentist->user->avatar)
                                        <img src="{{ asset('storage/' . $dentist->user->avatar) }}"
                                             alt="{{ $dentist->full_name }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-blue-700">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm text-slate-900">{{ $dentist->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $dentist->specialization ?: 'General Dentist' }}</p>
                                </div>

                                {{-- Fee --}}
                                @if($dentist->consultation_fee > 0)
                                    <span class="text-xs font-semibold text-blue-700 flex-shrink-0">
                                        ₱{{ number_format((float) $dentist->consultation_fee, 0) }}
                                    </span>
                                @endif

                                {{-- Select radio --}}
                                <input type="radio" name="dentist_id" value="{{ $dentist->id }}"
                                       x-model="dentistId"
                                       class="w-4 h-4 text-blue-700 border-slate-300 focus:ring-blue-600 flex-shrink-0">
                            </label>
                        @empty
                            <div class="px-4 py-4 border border-slate-200 rounded-xl bg-slate-50 text-sm text-slate-400 italic">
                                No doctors available at the moment. Please contact us directly.
                            </div>
                        @endforelse
                    </div>

                    {{-- Steps 3 & 4: Date & Time --}}
                    <div class="grid sm:grid-cols-2 gap-4 mb-7">
                        <div>
                            <h2 class="font-heading text-base font-bold text-slate-800 mb-3">3. Select Date</h2>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input type="date" name="appointment_date" x-model="date"
                                       min="{{ now()->addDay()->format('Y-m-d') }}"
                                       class="w-full pl-9 pr-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-sm text-slate-700 focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition-colors">
                            </div>
                        </div>

                        <div>
                            <h2 class="font-heading text-base font-bold text-slate-800 mb-3">4. Select Time</h2>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <select name="start_time" x-model="time"
                                        class="w-full pl-9 pr-10 py-3 border border-slate-200 rounded-xl bg-slate-50 text-sm text-slate-700 appearance-none focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition-colors">
                                    <option value="">— Pick a time —</option>
                                    @foreach($timeSlots as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 5: Your Information --}}
                    <div class="mb-6">
                        <h2 class="font-heading text-base font-bold text-slate-800 mb-4">5. Your Information</h2>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1.5">Full Name</label>
                                <input type="text" name="name"
                                       value="{{ auth()->user()?->name }}"
                                       placeholder="Juan dela Cruz"
                                       class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-700 placeholder-slate-300 focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1.5">Phone Number</label>
                                <input type="tel" name="phone"
                                       value="{{ auth()->user()?->phone }}"
                                       placeholder="+63 912 345 6789"
                                       class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-700 placeholder-slate-300 focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1.5">Email Address</label>
                                <input type="email" name="email"
                                       value="{{ auth()->user()?->email }}"
                                       placeholder="you@example.com"
                                       class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-700 placeholder-slate-300 focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1.5">Reason for Visit <span class="text-slate-300">(Optional)</span></label>
                                <input type="text" name="notes"
                                       placeholder="e.g. Routine checkup"
                                       class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-700 placeholder-slate-300 focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 transition-colors">
                            </div>
                        </div>
                    </div>

                    {{-- Security note --}}
                    <div class="flex items-center gap-2 text-xs text-slate-400 mt-2">
                        <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Your information is secure and encrypted.
                    </div>
                </div>

                {{-- Right: Appointment Summary --}}
                <div class="space-y-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                        <h3 class="font-heading text-lg font-bold text-slate-900 mb-5">Appointment Summary</h3>

                        <div class="space-y-4">
                            {{-- Service --}}
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Service</p>
                                    <p class="text-sm font-semibold text-slate-800" x-text="serviceName"></p>
                                    <p class="text-xs text-blue-700 font-medium"
                                       x-show="servicePrice"
                                       x-text="servicePrice ? formatPrice(servicePrice) : ''"></p>
                                </div>
                            </div>

                            {{-- Doctor --}}
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Doctor</p>
                                    <p class="text-sm font-semibold text-slate-800" x-text="dentistName"></p>
                                    <p class="text-xs text-slate-400"
                                       x-show="dentistSpec"
                                       x-text="dentistSpec || ''"></p>
                                </div>
                            </div>

                            {{-- Date --}}
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Date</p>
                                    <p class="text-sm font-semibold text-slate-800" x-text="formattedDate"></p>
                                </div>
                            </div>

                            {{-- Time --}}
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Time</p>
                                    <p class="text-sm font-semibold text-slate-800" x-text="formattedTime"></p>
                                </div>
                            </div>

                            {{-- Location --}}
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Location</p>
                                    <p class="text-sm font-semibold text-slate-800">{{ $settings->clinic_name }}</p>
                                    @if($settings->address)
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $settings->address }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- New patient badge --}}
                        <div class="mt-5 flex items-center gap-3 px-4 py-3 bg-blue-50 rounded-xl">
                            <svg class="w-5 h-5 text-blue-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-blue-800">New Patient Visit</p>
                                <p class="text-xs text-blue-700">We look forward to welcoming you!</p>
                            </div>
                        </div>

                        {{-- CTA --}}
                        <a href="{{ auth()->check() ? url('/') : route('register') }}"
                           class="mt-5 inline-flex items-center justify-center gap-2 w-full px-5 py-3.5 bg-blue-700 text-white text-sm font-semibold rounded-xl hover:bg-blue-800 shadow-md shadow-blue-200 transition-all duration-150"
                           wire:navigate>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Confirm Appointment
                        </a>

                        <p class="mt-3 text-center text-xs text-slate-400 flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Free cancellation up to 24 hours before your appointment.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Trust Badges --}}
    <section class="py-12 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid sm:grid-cols-3 gap-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-heading font-bold text-slate-900 text-sm">Insurance Accepted</p>
                        <p class="text-sm text-slate-500 mt-0.5">We work with most major insurance providers.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-heading font-bold text-slate-900 text-sm">Flexible Scheduling</p>
                        <p class="text-sm text-slate-500 mt-0.5">Early morning, evening &amp; weekend appointments.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-heading font-bold text-slate-900 text-sm">Urgent Care Available</p>
                        <p class="text-sm text-slate-500 mt-0.5">Same-day appointments for dental emergencies.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.guest>
