<div>
    <div class="mb-6">
        <h1 class="font-heading text-3xl font-bold text-slate-900">Book an Appointment</h1>
        <p class="text-slate-500 mt-1.5">Schedule your dental visit in a few easy steps</p>
    </div>

    @if($bookingComplete)
        {{-- Success State --}}
        <div class="text-center py-16 card p-8">
            <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-5">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 class="font-heading text-2xl font-bold text-slate-900">Appointment Booked!</h2>
            <p class="text-slate-600 mt-2">
                Your appointment <strong class="text-cyan-700 font-mono">{{ $appointmentNumber }}</strong> is pending confirmation.
            </p>
            <p class="text-sm text-slate-400 mt-1">We'll notify you once the clinic confirms your appointment.</p>
            <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
                <a href="{{ route('patient.appointments') }}"
                   class="btn-primary"
                   wire:navigate>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    View Appointments
                </a>
                <button wire:click="$set('bookingComplete', false); $set('step', 1)"
                        class="btn-secondary">
                    Book Another
                </button>
            </div>
        </div>
    @else
        {{-- Step Indicator --}}
        <nav aria-label="Booking progress" class="mb-8">
            <ol class="flex items-center gap-0">
                @foreach(['Service', 'Dentist', 'Date & Time', 'Confirm'] as $i => $label)
                    <li class="flex items-center {{ $i < 3 ? 'flex-1' : '' }}">
                        <button wire:click="goToStep({{ $i + 1 }})"
                                @if($step <= $i + 1) disabled @endif
                                aria-current="{{ $step === $i + 1 ? 'step' : 'false' }}"
                                class="flex items-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 rounded-lg p-1 -m-1 disabled:cursor-default">
                            <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-200
                                {{ $step === $i + 1 ? 'bg-cyan-600 text-white ring-4 ring-cyan-100' : ($step > $i + 1 ? 'bg-cyan-100 text-cyan-700' : 'bg-slate-100 text-slate-400') }}">
                                @if($step > $i + 1)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </span>
                            <span class="hidden sm:block text-sm font-medium {{ $step === $i + 1 ? 'text-cyan-700' : ($step > $i + 1 ? 'text-cyan-600' : 'text-slate-400') }}">
                                {{ $label }}
                            </span>
                        </button>
                        @if($i < 3)
                            <div class="flex-1 h-0.5 mx-3 {{ $step > $i + 1 ? 'bg-cyan-400' : 'bg-slate-200' }}" aria-hidden="true"></div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>

        {{-- Step 1: Select Service --}}
        @if($step === 1)
            <div class="card p-6" aria-live="polite">
                <h2 class="font-heading text-xl font-semibold text-slate-900 mb-5">Choose a Service</h2>

                @foreach($this->services->groupBy('category.name') as $category => $services)
                    <div class="mb-6">
                        <h3 class="section-label mb-3">{{ $category }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($services as $service)
                                <button wire:click="selectService({{ $service->id }})"
                                        class="text-left p-4 rounded-xl border-2 transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500
                                        {{ $selectedServiceId === $service->id
                                            ? 'border-cyan-500 bg-cyan-50 shadow-sm'
                                            : 'border-slate-200 hover:border-cyan-300 hover:bg-cyan-50/50' }}">
                                    <div class="flex items-start justify-between mb-2">
                                        <h4 class="font-semibold text-slate-900 text-sm leading-snug">{{ $service->name }}</h4>
                                        @if($selectedServiceId === $service->id)
                                            <svg class="w-4 h-4 text-cyan-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </div>
                                    @if($service->description)
                                        <p class="text-xs text-slate-500 line-clamp-2 mb-3">{{ $service->description }}</p>
                                    @endif
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold text-cyan-700 tabular-nums">₱{{ number_format($service->price, 2) }}</span>
                                        <span class="text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ $service->duration_minutes }} min</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Step 2: Select Dentist --}}
        @if($step === 2)
            <div class="card p-6" aria-live="polite">
                <h2 class="font-heading text-xl font-semibold text-slate-900 mb-5">Choose Your Dentist</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($this->dentists as $dentist)
                        <button wire:click="selectDentist({{ $dentist->id }})"
                                class="text-left p-5 rounded-xl border-2 transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500
                                {{ $selectedDentistId === $dentist->id
                                    ? 'border-cyan-500 bg-cyan-50 shadow-sm'
                                    : 'border-slate-200 hover:border-cyan-300 hover:bg-cyan-50/50' }}">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 rounded-full bg-cyan-100 flex items-center justify-center text-cyan-700 font-heading font-bold text-lg flex-shrink-0" aria-hidden="true">
                                    {{ strtoupper(substr($dentist->user->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-slate-900 truncate">Dr. {{ $dentist->user->name }}</h4>
                                    <p class="text-sm text-cyan-600">{{ $dentist->specialization ?? 'General Dentistry' }}</p>
                                </div>
                            </div>
                            @if($dentist->bio)
                                <p class="text-sm text-slate-500 line-clamp-2 mb-3">{{ $dentist->bio }}</p>
                            @endif
                            <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                                <span class="text-xs text-slate-400">{{ $dentist->license_number }}</span>
                                <span class="text-sm font-bold text-slate-700 tabular-nums">₱{{ number_format($dentist->consultation_fee, 2) }}<span class="text-xs font-normal text-slate-400">/visit</span></span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Step 3: Select Date & Time --}}
        @if($step === 3)
            <div class="card p-6" aria-live="polite">
                <h2 class="font-heading text-xl font-semibold text-slate-900 mb-5">Pick a Date & Time</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="date-picker" class="block text-sm font-medium text-slate-700 mb-2">
                            Select Date
                        </label>
                        <input id="date-picker"
                               type="date"
                               wire:model.live="selectedDate"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="input-field"
                               aria-describedby="date-hint">
                        <p id="date-hint" class="mt-1.5 text-xs text-slate-400">Appointments available from tomorrow onwards</p>
                    </div>

                    <div>
                        @if($selectedDate)
                            <p class="block text-sm font-medium text-slate-700 mb-2" id="slots-label">Available Times</p>
                            <div role="group" aria-labelledby="slots-label">
                                @if(count($this->availableSlots) > 0)
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach($this->availableSlots as $slot)
                                            <button wire:click="selectSlot('{{ $selectedDate }}', '{{ $slot }}')"
                                                    class="py-2.5 px-3 text-sm rounded-xl border-2 transition-all duration-150 font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500
                                                    {{ $selectedTime === $slot
                                                        ? 'border-cyan-500 bg-cyan-600 text-white shadow-sm'
                                                        : 'border-slate-200 hover:border-cyan-300 hover:bg-cyan-50 text-slate-700' }}"
                                                    :aria-pressed="{{ $selectedTime === $slot ? 'true' : 'false' }}">
                                                {{ date('g:i A', strtotime($slot)) }}
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                                        <p class="text-sm text-amber-700 font-medium">No available slots for this date.</p>
                                        <p class="text-xs text-amber-600 mt-1">Please try a different date.</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 h-full flex items-center justify-center">
                                <p class="text-sm text-slate-400 text-center">Select a date to see available time slots</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 4: Confirm --}}
        @if($step === 4)
            <div class="card p-6" aria-live="polite">
                <h2 class="font-heading text-xl font-semibold text-slate-900 mb-6">Confirm Your Appointment</h2>

                <div class="bg-cyan-50 rounded-xl border border-cyan-100 p-5 mb-6">
                    <h3 class="font-heading text-sm font-semibold text-cyan-800 mb-4">Appointment Summary</h3>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="section-label text-cyan-500">Service</dt>
                            <dd class="font-semibold text-slate-900 mt-0.5">
                                {{ \App\Models\Service::find($selectedServiceId)?->name ?? 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="section-label text-cyan-500">Dentist</dt>
                            <dd class="font-semibold text-slate-900 mt-0.5">
                                Dr. {{ \App\Models\Dentist::with('user')->find($selectedDentistId)?->user->name ?? 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="section-label text-cyan-500">Date</dt>
                            <dd class="font-semibold text-slate-900 mt-0.5">
                                {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('F d, Y') : 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="section-label text-cyan-500">Time</dt>
                            <dd class="font-semibold text-slate-900 mt-0.5">
                                {{ $selectedTime ? date('g:i A', strtotime($selectedTime)) : 'N/A' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="mb-6">
                    <label for="chief-complaint" class="block text-sm font-medium text-slate-700 mb-2">
                        Chief Complaint / Reason for Visit
                        <span class="text-slate-400 font-normal">(optional)</span>
                    </label>
                    <textarea id="chief-complaint"
                              wire:model="chiefComplaint"
                              rows="4"
                              class="input-field resize-none"
                              placeholder="Describe your dental concern or reason for this visit..."></textarea>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button wire:click="goToStep(3)" class="btn-secondary">
                        ← Back
                    </button>
                    <button wire:click="confirm"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-75 cursor-not-allowed"
                            class="btn-accent flex-1 sm:flex-none">
                        <span wire:loading.remove wire:target="confirm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Confirm Booking
                        </span>
                        <span wire:loading wire:target="confirm" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>
