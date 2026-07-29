<x-filament-panels::page>
    <div class="dcal">
        {{-- Toolbar --}}
        <div class="dcal-toolbar">
            <div class="dcal-toolbar-group">
                <x-filament::icon-button
                    icon="heroicon-o-chevron-left"
                    wire:click="previousWeek"
                    label="Previous week"
                />
                <x-filament::button color="gray" size="sm" wire:click="goToToday">
                    Today
                </x-filament::button>
                <x-filament::icon-button
                    icon="heroicon-o-chevron-right"
                    wire:click="nextWeek"
                    label="Next week"
                />
                <span class="dcal-range">{{ $this->getWeekRangeLabel() }}</span>
            </div>

            <div class="dcal-toolbar-group">
                <select wire:model.live="statusFilter" class="dcal-select">
                    <option value="">All Statuses</option>
                    @foreach ($this->getStatusOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select wire:model.live="dentistFilter" class="dcal-select">
                    <option value="">All Dentists</option>
                    @foreach ($this->getDentistOptions() as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>

                <x-filament::button
                    icon="heroicon-o-plus"
                    tag="a"
                    href="{{ \App\Filament\Resources\AppointmentResource::getUrl('create') }}"
                    size="sm"
                >
                    New Appointment
                </x-filament::button>
            </div>
        </div>

        {{-- Calendar grid --}}
        <div class="dcal-grid-wrap">
            <div class="dcal-grid">
                {{-- Day headers --}}
                <div class="dcal-corner"></div>
                @foreach ($this->getWeekDays() as $day)
                    <div class="dcal-day-head">
                        <div class="dcal-day-name">{{ $day->format('D') }}</div>
                        <div class="dcal-day-num {{ $day->isToday() ? 'is-today' : '' }}">
                            {{ $day->format('j') }}
                        </div>
                    </div>
                @endforeach

                {{-- Time labels --}}
                <div class="dcal-time-col" style="height: {{ $this->getGridHeight() }}px">
                    @foreach ($this->getHours() as $hour)
                        <div class="dcal-time-label" style="top: {{ ($hour - $this->getHours()[0]) * 60 }}px">
                            {{ \Carbon\Carbon::createFromTime($hour)->format('g A') }}
                        </div>
                    @endforeach
                </div>

                {{-- Day columns --}}
                @foreach ($this->getWeekDays() as $day)
                    <div class="dcal-day-col" style="height: {{ $this->getGridHeight() }}px">
                        @foreach ($this->getHours() as $hour)
                            <div class="dcal-hour-line" style="top: {{ ($hour - $this->getHours()[0]) * 60 }}px"></div>
                        @endforeach

                        @foreach ($this->layoutDay($this->getAppointmentsForDay($day)) as $item)
                            <button
                                type="button"
                                wire:click="mountAction('viewAppointment', { id: {{ $item['appointment']->id }} })"
                                class="dcal-appt status-{{ $item['appointment']->status->color() }}"
                                style="top: {{ $item['top'] }}px; height: {{ $item['height'] }}px; left: {{ $item['left'] }}%; width: calc({{ $item['width'] }}% - 4px)"
                                title="{{ $item['appointment']->patient->full_name }} — {{ $item['appointment']->status->label() }}"
                            >
                                <div class="dcal-appt-title">{{ $item['appointment']->patient->full_name }}</div>
                                @if ($item['height'] >= 40)
                                    <div class="dcal-appt-sub">
                                        {{ \Carbon\Carbon::parse($item['appointment']->start_time)->format('g:i A') }}
                                        · {{ $item['appointment']->service?->display_name }}
                                    </div>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Legend --}}
        <div class="dcal-legend">
            @foreach (\App\Enums\AppointmentStatus::cases() as $status)
                <div class="dcal-legend-item">
                    <span class="dcal-dot status-{{ $status->color() }}"></span>
                    {{ $status->label() }}
                </div>
            @endforeach
        </div>
    </div>

    <style>
        .dcal { display: flex; flex-direction: column; gap: 1rem; }

        .dcal-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; }
        .dcal-toolbar-group { display: flex; align-items: center; gap: 0.5rem; }
        .dcal-range { margin-left: 0.5rem; font-size: 0.875rem; font-weight: 600; color: rgb(9 9 11); }
        .dark .dcal-range { color: rgb(250 250 250); }

        .dcal-select {
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            font-size: 0.875rem;
            background: #fff;
            color: rgb(9 9 11);
            color-scheme: light;
        }
        .dcal-select option { background: #fff; color: rgb(9 9 11); }
        .dark .dcal-select {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.2);
            color: rgb(250 250 250);
            color-scheme: dark;
        }
        .dark .dcal-select option { background: rgb(24 24 27); color: rgb(250 250 250); }

        .dcal-grid-wrap {
            overflow-x: auto;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.75rem;
            background: #fff;
        }
        .dark .dcal-grid-wrap { background: rgb(17 24 39); border-color: rgba(255, 255, 255, 0.1); }

        .dcal-grid { display: grid; grid-template-columns: 60px repeat(7, minmax(120px, 1fr)); min-width: 900px; }

        .dcal-corner { border-bottom: 1px solid rgba(0, 0, 0, 0.1); }
        .dark .dcal-corner { border-color: rgba(255, 255, 255, 0.1); }

        .dcal-day-head {
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            border-left: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0.5rem;
            text-align: center;
        }
        .dark .dcal-day-head { border-color: rgba(255, 255, 255, 0.1); }
        .dcal-day-name { font-size: 0.75rem; color: rgb(107 114 128); }
        .dark .dcal-day-name { color: rgb(156 163 175); }
        .dcal-day-num {
            margin: 0.25rem auto 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(9 9 11);
        }
        .dark .dcal-day-num { color: rgb(250 250 250); }
        .dcal-day-num.is-today { background: rgb(8 145 178); color: #fff; }

        .dcal-time-col { position: relative; }
        .dcal-time-label {
            position: absolute;
            right: 0.5rem;
            left: 0;
            transform: translateY(-50%);
            text-align: right;
            padding-right: 0.5rem;
            font-size: 0.75rem;
            color: rgb(156 163 175);
        }

        .dcal-day-col { position: relative; border-left: 1px solid rgba(0, 0, 0, 0.1); }
        .dark .dcal-day-col { border-color: rgba(255, 255, 255, 0.1); }
        .dcal-hour-line { position: absolute; left: 0; right: 0; border-top: 1px solid rgba(0, 0, 0, 0.06); }
        .dark .dcal-hour-line { border-color: rgba(255, 255, 255, 0.08); }

        .dcal-appt {
            position: absolute;
            display: block;
            overflow: hidden;
            appearance: none;
            -webkit-appearance: none;
            background: none;
            border: none;
            border-left: 4px solid;
            border-radius: 0.375rem;
            padding: 2px 0.5rem;
            font: inherit;
            font-size: 0.75rem;
            line-height: 1.15;
            text-align: left;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            text-decoration: none;
            transition: box-shadow 0.15s ease;
        }
        .dcal-appt:hover { box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15); }
        .dcal-appt-title { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dcal-appt-sub { font-size: 0.688rem; opacity: 0.8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .dcal-legend { display: flex; flex-wrap: wrap; gap: 1rem; font-size: 0.75rem; color: rgb(107 114 128); }
        .dark .dcal-legend { color: rgb(156 163 175); }
        .dcal-legend-item { display: flex; align-items: center; gap: 0.375rem; }
        .dcal-dot { width: 0.625rem; height: 0.625rem; border-radius: 9999px; border: 2px solid; display: inline-block; }

        /* Status colors */
        .status-warning { border-color: #f59e0b; background: #fffbeb; color: #78350f; }
        .dark .status-warning { background: rgba(120, 53, 15, 0.35); color: #fde68a; }

        .status-info { border-color: #38bdf8; background: #f0f9ff; color: #0c4a6e; }
        .dark .status-info { background: rgba(12, 74, 110, 0.35); color: #bae6fd; }

        .status-success { border-color: #34d399; background: #ecfdf5; color: #065f46; }
        .dark .status-success { background: rgba(6, 95, 70, 0.35); color: #a7f3d0; }

        .status-danger { border-color: #fb7185; background: #fff1f2; color: #881337; }
        .dark .status-danger { background: rgba(136, 19, 55, 0.35); color: #fecdd3; }

        .status-primary { border-color: #22d3ee; background: #ecfeff; color: #164e63; }
        .dark .status-primary { background: rgba(22, 78, 99, 0.35); color: #a5f3fc; }

        .status-gray { border-color: #9ca3af; background: #f9fafb; color: #374151; }
        .dark .status-gray { background: rgba(255, 255, 255, 0.06); color: #d1d5db; }
    </style>
</x-filament-panels::page>
