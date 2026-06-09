<div>
    {{-- Page Header --}}
    <div class="mb-8 flex items-center gap-5">
        {{-- Avatar --}}
        @if($this->patient?->photo)
            <img src="{{ $this->patient->photoUrl() }}"
                 alt="{{ $this->patient->first_name }}"
                 class="w-16 h-16 rounded-full object-cover border-4 border-white shadow-md flex-shrink-0">
        @else
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-cyan-400 to-teal-500 flex items-center justify-center border-4 border-white shadow-md flex-shrink-0">
                <span class="text-2xl font-bold text-white font-heading">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
            </div>
        @endif

        <div>
        <h1 class="font-heading text-3xl font-bold text-slate-900">
            Welcome back, {{ auth()->user()->name }}
        </h1>
        @if($this->patient)
            <p class="text-slate-500 mt-1.5">Patient ID: <span class="font-medium text-cyan-700">{{ $this->patient->patient_number }}</span></p>
        @else
            <div class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-lg">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.834-1.964-.834-2.732 0L3.07 16.5C2.3 17.333 3.262 19 4.802 19z"/>
                </svg>
                <p class="text-sm text-amber-700 font-medium">Your patient profile is being set up. Please contact the clinic.</p>
            </div>
        @endif
        </div>
    </div>

    @if($this->patient)

    {{-- Cleaning Reminder Banner --}}
    @if($this->patient->next_cleaning_due)
        @php
            $due = $this->patient->next_cleaning_due;
            $daysUntil = now()->diffInDays($due, false); // negative = overdue
        @endphp
        @if($daysUntil <= 30)
            <div class="mb-6 flex items-start gap-4 p-5 rounded-2xl border
                {{ $daysUntil < 0
                    ? 'bg-red-50 border-red-200'
                    : 'bg-amber-50 border-amber-200' }}">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center
                    {{ $daysUntil < 0 ? 'bg-red-100' : 'bg-amber-100' }}">
                    <svg class="w-5 h-5 {{ $daysUntil < 0 ? 'text-red-600' : 'text-amber-600' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-sm {{ $daysUntil < 0 ? 'text-red-800' : 'text-amber-800' }}">
                        {{ $daysUntil < 0 ? 'Dental Cleaning Overdue!' : 'Dental Cleaning Reminder' }}
                    </p>
                    <p class="text-sm mt-0.5 {{ $daysUntil < 0 ? 'text-red-700' : 'text-amber-700' }}">
                        {{ $daysUntil < 0
                            ? 'Your 6-month cleaning was due on ' . $due->format('F d, Y') . '. Please book an appointment.'
                            : 'Your next dental cleaning is due on ' . $due->format('F d, Y') . ' (' . $due->diffForHumans() . ').' }}
                    </p>
                </div>
                <a href="{{ route('patient.book') }}"
                   wire:navigate
                   class="flex-shrink-0 px-4 py-2 text-xs font-semibold rounded-xl
                       {{ $daysUntil < 0
                           ? 'bg-red-600 hover:bg-red-700 text-white'
                           : 'bg-amber-500 hover:bg-amber-600 text-white' }}
                       transition-colors duration-150">
                    Book Now
                </a>
            </div>
        @endif
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Upcoming Appointments --}}
        <div class="lg:col-span-2 card p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-heading text-lg font-semibold text-slate-900">Upcoming Appointments</h2>
                <a href="{{ route('patient.appointments') }}"
                   class="text-sm text-cyan-600 font-medium hover:text-cyan-700 hover:underline"
                   wire:navigate>View all</a>
            </div>

            @forelse($this->upcomingAppointments as $appointment)
                <div class="flex items-start gap-4 py-4 border-b border-slate-100 last:border-0">
                    <div class="flex-shrink-0 w-14 h-14 bg-cyan-50 rounded-xl flex flex-col items-center justify-center border border-cyan-100" aria-hidden="true">
                        <span class="text-xs font-semibold text-cyan-500 leading-none">{{ $appointment->appointment_date->format('M') }}</span>
                        <span class="text-2xl font-bold text-cyan-700 leading-tight">{{ $appointment->appointment_date->format('d') }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-900 truncate">{{ $appointment->service->display_name ?? 'Service' }}</p>
                        <p class="text-sm text-slate-500 mt-0.5">
                            Dr. {{ $appointment->dentist->user->name ?? 'N/A' }}
                            <span class="text-slate-300 mx-1" aria-hidden="true">·</span>
                            {{ date('g:i A', strtotime($appointment->start_time)) }}
                        </p>
                    </div>
                    @php
                        $statusColor = match($appointment->status->value) {
                            'confirmed' => 'bg-green-100 text-green-700',
                            'pending' => 'bg-amber-100 text-amber-700',
                            'in_progress' => 'bg-cyan-100 text-cyan-700',
                            default => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <span class="badge {{ $statusColor }} flex-shrink-0">{{ $appointment->status->label() }}</span>
                </div>
            @empty
                <div class="text-center py-10">
                    <div class="w-14 h-14 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 text-sm">No upcoming appointments</p>
                    <a href="{{ route('patient.book') }}"
                       class="mt-3 inline-flex items-center gap-1 text-sm text-cyan-600 font-medium hover:text-cyan-700 hover:underline"
                       wire:navigate>
                        Book one now
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">
            {{-- Quick Actions --}}
            <div class="bg-gradient-to-br from-cyan-600 to-teal-700 rounded-2xl p-6 text-white shadow-md">
                <h3 class="font-heading font-semibold text-lg mb-4">Quick Actions</h3>
                <div class="space-y-2.5">
                    <a href="{{ route('patient.book') }}"
                       class="flex items-center gap-2.5 w-full py-2.5 px-4 bg-white/15 hover:bg-white/25 rounded-xl text-sm font-medium transition-all duration-150"
                       wire:navigate>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Book Appointment
                    </a>
                    <a href="{{ route('patient.records') }}"
                       class="flex items-center gap-2.5 w-full py-2.5 px-4 bg-white/15 hover:bg-white/25 rounded-xl text-sm font-medium transition-all duration-150"
                       wire:navigate>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Medical Records
                    </a>
                    <a href="{{ route('patient.invoices') }}"
                       class="flex items-center gap-2.5 w-full py-2.5 px-4 bg-white/15 hover:bg-white/25 rounded-xl text-sm font-medium transition-all duration-150"
                       wire:navigate>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                        My Invoices
                    </a>
                </div>
            </div>

            {{-- Pending Payments --}}
            @if($this->unpaidInvoices->isNotEmpty())
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.834-1.964-.834-2.732 0L3.07 16.5C2.3 17.333 3.262 19 4.802 19z"/>
                    </svg>
                    <h3 class="font-heading font-semibold text-amber-800 text-sm">Pending Payments</h3>
                </div>
                @foreach($this->unpaidInvoices as $invoice)
                    <div class="flex justify-between items-center py-2.5 border-b border-amber-100 last:border-0">
                        <div>
                            <p class="text-sm font-semibold text-amber-900">{{ $invoice->invoice_number }}</p>
                            <p class="text-xs text-amber-600 mt-0.5">Due: {{ $invoice->due_date?->format('M d, Y') ?? 'N/A' }}</p>
                        </div>
                        <span class="text-sm font-bold text-amber-800 tabular-nums">₱{{ number_format($invoice->balance_due, 2) }}</span>
                    </div>
                @endforeach
            </div>
            @endif

            {{-- Recent Visits --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-heading font-semibold text-slate-900">Recent Visits</h3>
                    <a href="{{ route('patient.records') }}"
                       class="text-xs text-cyan-600 font-medium hover:underline"
                       wire:navigate>All records</a>
                </div>
                @forelse($this->recentRecords as $record)
                    <div class="py-2.5 border-b border-slate-100 last:border-0">
                        <p class="text-sm font-semibold text-slate-900">{{ $record->visit_date->format('M d, Y') }}</p>
                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ Str::limit($record->diagnosis, 70) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 text-center py-4">No records yet</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>
