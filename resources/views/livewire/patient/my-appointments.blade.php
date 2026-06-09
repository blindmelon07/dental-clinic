<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="font-heading text-3xl font-bold text-slate-900">My Appointments</h1>
            <p class="text-slate-500 mt-1.5">View and manage your dental appointments</p>
        </div>
        <a href="{{ route('patient.book') }}"
           class="btn-primary self-start"
           wire:navigate>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Book New
        </a>
    </div>

    {{-- Filters --}}
    <div class="card px-4 py-3 mb-5 flex items-center gap-3">
        <label for="status-filter" class="text-sm font-medium text-slate-600 flex-shrink-0">Filter:</label>
        <select id="status-filter"
                wire:model.live="statusFilter"
                class="input-field max-w-xs">
            <option value="">All Statuses</option>
            @foreach(\App\Enums\AppointmentStatus::cases() as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>

    @if($this->patient)
        <div class="space-y-3" aria-live="polite" aria-label="Appointments list">
            @forelse($this->appointments as $appointment)
                <article class="card p-5 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex gap-4">
                            {{-- Date badge --}}
                            <div class="flex-shrink-0 text-center w-14" aria-hidden="true">
                                <div class="bg-cyan-50 rounded-xl p-2.5 border border-cyan-100">
                                    <p class="text-xs font-bold text-cyan-500 uppercase leading-none">{{ $appointment->appointment_date->format('M') }}</p>
                                    <p class="text-2xl font-bold text-cyan-700 leading-tight mt-0.5">{{ $appointment->appointment_date->format('d') }}</p>
                                    <p class="text-xs text-cyan-400 leading-none">{{ $appointment->appointment_date->format('D') }}</p>
                                </div>
                            </div>

                            {{-- Details --}}
                            <div class="min-w-0">
                                <h2 class="font-heading font-semibold text-slate-900">{{ $appointment->service->display_name ?? 'N/A' }}</h2>
                                <p class="text-sm text-slate-500 mt-0.5">
                                    Dr. {{ $appointment->dentist->user->name ?? 'N/A' }}
                                    <span class="text-slate-300 mx-1" aria-hidden="true">·</span>
                                    <time datetime="{{ $appointment->appointment_date->toDateString() }}T{{ $appointment->start_time }}">
                                        {{ date('g:i A', strtotime($appointment->start_time)) }}
                                    </time>
                                    –
                                    <time>{{ date('g:i A', strtotime($appointment->end_time)) }}</time>
                                </p>
                                <p class="text-xs text-slate-400 font-mono mt-1">{{ $appointment->appointment_number }}</p>
                                @if($appointment->chief_complaint)
                                    <p class="text-sm text-slate-600 mt-2 italic line-clamp-2">"{{ Str::limit($appointment->chief_complaint, 100) }}"</p>
                                @endif
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-col items-end gap-2 flex-shrink-0">
                            @php
                                $statusColor = match($appointment->status->value) {
                                    'confirmed' => 'bg-green-100 text-green-700',
                                    'pending' => 'bg-amber-100 text-amber-700',
                                    'completed' => 'bg-cyan-100 text-cyan-700',
                                    'cancelled', 'no_show' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <span class="badge {{ $statusColor }}">{{ $appointment->status->label() }}</span>

                            @if(in_array($appointment->status->value, ['pending', 'confirmed']))
                                <button wire:click="openCancelModal({{ $appointment->id }})"
                                        class="text-xs text-red-500 hover:text-red-700 font-medium hover:underline transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-400 rounded">
                                    Cancel
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="text-center py-16 card p-8">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-heading font-semibold text-slate-700">No appointments found</h3>
                    <p class="text-slate-500 text-sm mt-1">
                        @if($statusFilter) No appointments match the selected filter. @else You haven't booked any appointments yet. @endif
                    </p>
                    @if(!$statusFilter)
                        <a href="{{ route('patient.book') }}"
                           class="mt-4 inline-flex items-center gap-1.5 text-sm text-cyan-600 font-medium hover:text-cyan-700 hover:underline"
                           wire:navigate>
                            Book your first appointment
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $this->appointments->links() }}
        </div>
    @else
        <div class="text-center py-16 card p-8">
            <p class="text-slate-500">Patient profile not found. Please contact the clinic to set up your profile.</p>
        </div>
    @endif

    {{-- Cancel Confirmation Modal --}}
    @if($showCancelModal)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-data
        x-on:keydown.escape.window="$wire.closeCancelModal()"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cancel-modal-title"
    >
        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
            wire:click="closeCancelModal"
        ></div>

        {{-- Modal panel --}}
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl flex flex-col max-h-[90vh]">

            {{-- Red top bar --}}
            <div class="h-1.5 bg-gradient-to-r from-red-400 to-red-600 flex-shrink-0 rounded-t-2xl"></div>

            <div class="p-6 overflow-y-auto flex-1">
                {{-- Icon + heading --}}
                <div class="flex items-start gap-4 mb-5">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="cancel-modal-title" class="font-heading text-lg font-bold text-slate-900">
                            Cancel Appointment?
                        </h2>
                        <p class="text-sm text-slate-500 mt-0.5">
                            Please review the details below before confirming.
                        </p>
                    </div>
                </div>

                {{-- Appointment details box --}}
                <div class="bg-slate-50 rounded-xl border border-slate-200 divide-y divide-slate-100 mb-5 text-sm">
                    <div class="flex items-center gap-3 px-4 py-3">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                        </svg>
                        <span class="text-slate-500 w-28 flex-shrink-0">Ref #</span>
                        <span class="font-mono font-semibold text-slate-800">{{ $cancelAppointmentNumber }}</span>
                    </div>
                    <div class="flex items-center gap-3 px-4 py-3">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="text-slate-500 w-28 flex-shrink-0">Service</span>
                        <span class="font-medium text-slate-800">{{ $cancelServiceName }}</span>
                    </div>
                    <div class="flex items-center gap-3 px-4 py-3">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-slate-500 w-28 flex-shrink-0">Date & Time</span>
                        <span class="font-medium text-slate-800">{{ $cancelDate }}</span>
                    </div>
                    <div class="flex items-center gap-3 px-4 py-3">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-slate-500 w-28 flex-shrink-0">Dentist</span>
                        <span class="font-medium text-slate-800">{{ $cancelDentist }}</span>
                    </div>
                </div>

                {{-- Reason textarea --}}
                <div class="mb-4">
                    <label for="cancel-reason" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Reason for cancellation <span class="text-slate-400 font-normal">(optional)</span>
                    </label>
                    <textarea
                        id="cancel-reason"
                        wire:model="cancelReason"
                        rows="3"
                        placeholder="e.g. Schedule conflict, feeling unwell, need to reschedule…"
                        class="input-field resize-none"
                        maxlength="255"
                    ></textarea>
                </div>

                {{-- Warning note --}}
                <div class="flex items-start gap-2.5 bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-6">
                    <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-sm text-red-700">
                        <span class="font-semibold">This action cannot be undone.</span>
                        Once cancelled, you will need to book a new appointment.
                    </p>
                </div>

            </div>

            {{-- Action buttons — always visible at bottom --}}
            <div class="flex gap-3 px-6 py-4 border-t border-slate-100 bg-white flex-shrink-0 rounded-b-2xl">
                <button
                    wire:click="closeCancelModal"
                    class="flex-1 px-4 py-2.5 border border-slate-300 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400">
                    Keep Appointment
                </button>
                <button
                    wire:click="confirmCancel"
                    wire:loading.attr="disabled"
                    wire:target="confirmCancel"
                    class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="confirmCancel">Yes, Cancel It</span>
                    <span wire:loading wire:target="confirmCancel" class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Cancelling…
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
