<div>
    <div class="mb-8">
        <h1 class="font-heading text-3xl font-bold text-slate-900">Medical Records</h1>
        <p class="text-slate-500 mt-1.5">Your complete dental health history</p>
    </div>

    @if($this->selectedRecord)
        {{-- Record Detail View --}}
        <div class="card p-6">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-heading text-xl font-semibold text-slate-900">
                        Visit — {{ $this->selectedRecord->visit_date->format('F d, Y') }}
                    </h2>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Dr. {{ $this->selectedRecord->dentist->user->name ?? 'N/A' }}
                        @if($this->selectedRecord->appointment)
                            <span class="text-slate-300 mx-1" aria-hidden="true">·</span>
                            <span class="font-mono">{{ $this->selectedRecord->appointment->appointment_number }}</span>
                        @endif
                    </p>
                </div>
                <button wire:click="closeRecord"
                        class="btn-secondary self-start">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Records
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach([
                    'Chief Complaint' => $this->selectedRecord->chief_complaint,
                    'Diagnosis' => $this->selectedRecord->diagnosis,
                    'Treatment Plan' => $this->selectedRecord->treatment_plan,
                    'Treatment Done' => $this->selectedRecord->treatment_done,
                    'Prescription' => $this->selectedRecord->prescription,
                    'Notes' => $this->selectedRecord->notes,
                ] as $label => $value)
                    @if($value)
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <h3 class="section-label mb-2">{{ $label }}</h3>
                        <p class="text-sm text-slate-800 whitespace-pre-line leading-relaxed">{{ $value }}</p>
                    </div>
                    @endif
                @endforeach
            </div>

            @if($this->selectedRecord->next_visit_recommendation)
                <div class="mt-4 p-4 bg-cyan-50 rounded-xl border border-cyan-100 flex gap-3">
                    <svg class="w-5 h-5 text-cyan-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div>
                        <h3 class="text-xs font-semibold text-cyan-700 uppercase tracking-widest mb-1">Next Visit Recommendation</h3>
                        <p class="text-sm text-cyan-900">{{ $this->selectedRecord->next_visit_recommendation }}</p>
                    </div>
                </div>
            @endif

            @if($this->selectedRecord->xrays->isNotEmpty())
                <div class="mt-6">
                    <h3 class="font-heading font-semibold text-slate-900 mb-4">
                        X-Ray Images
                        <span class="ml-2 badge bg-slate-100 text-slate-600">{{ $this->selectedRecord->xrays->count() }}</span>
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach($this->selectedRecord->xrays as $xray)
                            <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
                                <img src="{{ asset('storage/' . $xray->file_path) }}"
                                     alt="{{ ucfirst($xray->xray_type ?? 'X-Ray') }} image"
                                     class="w-full h-40 object-cover"
                                     loading="lazy">
                                <div class="p-3">
                                    <p class="text-xs font-semibold text-slate-700">{{ ucfirst($xray->xray_type ?? 'X-Ray') }}</p>
                                    @if($xray->findings)
                                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $xray->findings }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

    @else
        {{-- Records List --}}
        @if($this->patient)
            <div class="space-y-3" aria-live="polite" aria-label="Medical records list">
                @forelse($this->records as $record)
                    <article class="card p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex gap-4 min-w-0">
                                <div class="flex-shrink-0 w-12 h-12 bg-cyan-50 rounded-xl flex items-center justify-center border border-cyan-100" aria-hidden="true">
                                    <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="font-heading font-semibold text-slate-900">
                                        <time datetime="{{ $record->visit_date->toDateString() }}">{{ $record->visit_date->format('F d, Y') }}</time>
                                    </h2>
                                    <p class="text-sm text-slate-500 mt-0.5">Dr. {{ $record->dentist->user->name ?? 'N/A' }}</p>
                                    <p class="text-sm text-slate-600 mt-2 line-clamp-2">{{ Str::limit($record->diagnosis, 120) }}</p>
                                    @if($record->treatment_done)
                                        <p class="text-xs text-slate-400 mt-1.5">
                                            <span class="font-medium">Treatment:</span>
                                            {{ Str::limit($record->treatment_done, 80) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                <button wire:click="viewRecord({{ $record->id }})"
                                        class="text-sm text-cyan-600 border border-cyan-200 rounded-xl px-4 py-2 hover:bg-cyan-50 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-500 font-medium">
                                    View Details
                                </button>
                                @if($record->xrays->isNotEmpty())
                                    <span class="text-xs text-slate-400 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $record->xrays->count() }} X-Ray(s)
                                    </span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="text-center py-16 card p-8">
                        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="font-heading font-semibold text-slate-700">No medical records yet</h3>
                        <p class="text-slate-500 text-sm mt-1">Your dental records will appear here after your visits.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $this->records->links() }}
            </div>
        @endif
    @endif
</div>
