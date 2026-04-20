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
                                <h2 class="font-heading font-semibold text-slate-900">{{ $appointment->service->name ?? 'N/A' }}</h2>
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
                                <button wire:click="cancelAppointment({{ $appointment->id }})"
                                        wire:confirm="Are you sure you want to cancel this appointment? This action cannot be undone."
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
</div>
