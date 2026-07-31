<x-filament-panels::page>
    <div class="rpt">
        {{-- Date range toolbar --}}
        <div class="rpt-toolbar">
            <div class="rpt-presets">
                <x-filament::button size="sm" color="gray" wire:click="applyPreset('today')">Today</x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="applyPreset('this_week')">This Week</x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="applyPreset('this_month')">This Month</x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="applyPreset('this_year')">This Year</x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="applyPreset('all_time')">All Time</x-filament::button>
            </div>
            <div class="rpt-range">
                <label>From <input type="date" wire:model.live="startDate" class="rpt-date-input"></label>
                <label>To <input type="date" wire:model.live="endDate" class="rpt-date-input"></label>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="rpt-tabs-row">
            <div class="rpt-tabs">
                <button type="button" wire:click="setTab('revenue')" @class(['rpt-tab', 'is-active' => $activeTab === 'revenue'])>Revenue</button>
                <button type="button" wire:click="setTab('appointments')" @class(['rpt-tab', 'is-active' => $activeTab === 'appointments'])>Appointments</button>
                <button type="button" wire:click="setTab('patients')" @class(['rpt-tab', 'is-active' => $activeTab === 'patients'])>Patients &amp; Records</button>
                <button type="button" wire:click="setTab('inventory')" @class(['rpt-tab', 'is-active' => $activeTab === 'inventory'])>Inventory</button>
            </div>

            <x-filament::button
                size="sm"
                color="gray"
                icon="heroicon-o-arrow-down-tray"
                wire:click="{{ match ($activeTab) {
                    'appointments' => 'exportAppointments',
                    'patients' => 'exportPatients',
                    'inventory' => 'exportInventory',
                    default => 'exportRevenue',
                } }}"
            >
                Export CSV
            </x-filament::button>
        </div>

        {{-- Revenue --}}
        @if ($activeTab === 'revenue')
            @php $summary = $this->getRevenueSummary(); @endphp
            <div class="rpt-stats">
                <div class="rpt-stat">
                    <div class="rpt-stat-label">Total Collected</div>
                    <div class="rpt-stat-value">₱{{ number_format($summary['totalCollected'], 2) }}</div>
                </div>
                <div class="rpt-stat">
                    <div class="rpt-stat-label">Total Invoiced</div>
                    <div class="rpt-stat-value">₱{{ number_format($summary['totalInvoiced'], 2) }}</div>
                </div>
                <div class="rpt-stat">
                    <div class="rpt-stat-label">Payments Recorded</div>
                    <div class="rpt-stat-value">{{ $summary['paymentsCount'] }}</div>
                </div>
                <div class="rpt-stat">
                    <div class="rpt-stat-label">Outstanding Balance</div>
                    <div class="rpt-stat-value">₱{{ number_format($summary['outstanding'], 2) }}</div>
                </div>
            </div>

            <div class="rpt-grid">
                <x-filament::section heading="Revenue by Month">
                    @php $byMonth = $this->getRevenueByMonth(); @endphp
                    @if ($byMonth->isEmpty())
                        <p class="rpt-empty">No payments recorded in this range.</p>
                    @else
                        <table class="rpt-table">
                            <thead><tr><th>Month</th><th>Payments</th><th>Total</th></tr></thead>
                            <tbody>
                                @foreach ($byMonth as $row)
                                    <tr>
                                        <td>{{ $row['label'] }}</td>
                                        <td>{{ $row['count'] }}</td>
                                        <td>₱{{ number_format($row['total'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </x-filament::section>

                <x-filament::section heading="Revenue by Payment Method">
                    @php $byMethod = $this->getRevenueByMethod(); @endphp
                    @if ($byMethod->isEmpty())
                        <p class="rpt-empty">No payments recorded in this range.</p>
                    @else
                        <table class="rpt-table">
                            <thead><tr><th>Method</th><th>Count</th><th>Total</th></tr></thead>
                            <tbody>
                                @foreach ($byMethod as $row)
                                    <tr>
                                        <td>{{ $row['label'] }}</td>
                                        <td>{{ $row['count'] }}</td>
                                        <td>₱{{ number_format($row['total'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </x-filament::section>
            </div>

            <x-filament::section heading="Invoices in Range">
                @php $invoices = $this->getRecentInvoices(); @endphp
                @if ($invoices->isEmpty())
                    <p class="rpt-empty">No invoices in this range.</p>
                @else
                    <table class="rpt-table">
                        <thead><tr><th>Invoice #</th><th>Patient</th><th>Date</th><th>Status</th><th>Total</th></tr></thead>
                        <tbody>
                            @foreach ($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ $invoice->patient?->full_name ?? '—' }}</td>
                                    <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                    <td><x-filament::badge :color="$invoice->status->color()">{{ $invoice->status->label() }}</x-filament::badge></td>
                                    <td>₱{{ number_format($invoice->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </x-filament::section>
        @endif

        {{-- Appointments --}}
        @if ($activeTab === 'appointments')
            @php $summary = $this->getAppointmentSummary(); @endphp
            <div class="rpt-stats">
                <div class="rpt-stat">
                    <div class="rpt-stat-label">Total Appointments</div>
                    <div class="rpt-stat-value">{{ $summary['total'] }}</div>
                </div>
            </div>

            <div class="rpt-grid">
                <x-filament::section heading="By Status">
                    @if ($summary['byStatus']->isEmpty())
                        <p class="rpt-empty">No appointments in this range.</p>
                    @else
                        <table class="rpt-table">
                            <thead><tr><th>Status</th><th>Count</th></tr></thead>
                            <tbody>
                                @foreach ($summary['byStatus'] as $row)
                                    <tr>
                                        <td><x-filament::badge :color="$row['color']">{{ $row['label'] }}</x-filament::badge></td>
                                        <td>{{ $row['count'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </x-filament::section>

                <x-filament::section heading="By Dentist">
                    @php $byDentist = $this->getAppointmentsByDentist(); @endphp
                    @if ($byDentist->isEmpty())
                        <p class="rpt-empty">No appointments in this range.</p>
                    @else
                        <table class="rpt-table">
                            <thead><tr><th>Dentist</th><th>Total</th><th>Completed</th></tr></thead>
                            <tbody>
                                @foreach ($byDentist as $row)
                                    <tr>
                                        <td>{{ $row['name'] }}</td>
                                        <td>{{ $row['count'] }}</td>
                                        <td>{{ $row['completed'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </x-filament::section>
            </div>
        @endif

        {{-- Patients & Records --}}
        @if ($activeTab === 'patients')
            @php $summary = $this->getPatientRecordSummary(); @endphp
            <div class="rpt-stats">
                <div class="rpt-stat">
                    <div class="rpt-stat-label">New Patients</div>
                    <div class="rpt-stat-value">{{ $summary['newPatients'] }}</div>
                </div>
                <div class="rpt-stat">
                    <div class="rpt-stat-label">Dental Visits</div>
                    <div class="rpt-stat-value">{{ $summary['visits'] }}</div>
                </div>
            </div>

            <x-filament::section heading="Top Diagnoses">
                @php $diagnoses = $this->getTopDiagnoses(); @endphp
                @if ($diagnoses->isEmpty())
                    <p class="rpt-empty">No dental records in this range.</p>
                @else
                    <table class="rpt-table">
                        <thead><tr><th>Diagnosis</th><th>Occurrences</th></tr></thead>
                        <tbody>
                            @foreach ($diagnoses as $row)
                                <tr>
                                    <td>{{ $row['name'] }}</td>
                                    <td>{{ $row['count'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </x-filament::section>
        @endif

        {{-- Inventory --}}
        @if ($activeTab === 'inventory')
            @php $summary = $this->getInventorySummary(); @endphp
            <div class="rpt-stats">
                <div class="rpt-stat">
                    <div class="rpt-stat-label">Low Stock Medicines</div>
                    <div class="rpt-stat-value">{{ $summary['lowStock'] }}</div>
                </div>
                <div class="rpt-stat">
                    <div class="rpt-stat-label">Dispensed Quantity</div>
                    <div class="rpt-stat-value">{{ $summary['dispensedQty'] }}</div>
                </div>
                <div class="rpt-stat">
                    <div class="rpt-stat-label">Dispensed Cost</div>
                    <div class="rpt-stat-value">₱{{ number_format($summary['dispensedCost'], 2) }}</div>
                </div>
            </div>

            <div class="rpt-grid">
                <x-filament::section heading="Low Stock Medicines">
                    @php $lowStock = $this->getLowStockMedicines(); @endphp
                    @if ($lowStock->isEmpty())
                        <p class="rpt-empty">Nothing is low on stock.</p>
                    @else
                        <table class="rpt-table">
                            <thead><tr><th>Medicine</th><th>Stock</th><th>Minimum</th></tr></thead>
                            <tbody>
                                @foreach ($lowStock as $medicine)
                                    <tr>
                                        <td>{{ $medicine->display_name }}</td>
                                        <td>{{ $medicine->current_stock }} {{ $medicine->unit }}</td>
                                        <td>{{ $medicine->minimum_stock }} {{ $medicine->unit }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </x-filament::section>

                <x-filament::section heading="Top Dispensed Medicines">
                    @php $topDispensed = $this->getTopDispensedMedicines(); @endphp
                    @if ($topDispensed->isEmpty())
                        <p class="rpt-empty">No medicines dispensed in this range.</p>
                    @else
                        <table class="rpt-table">
                            <thead><tr><th>Medicine</th><th>Qty</th><th>Cost</th></tr></thead>
                            <tbody>
                                @foreach ($topDispensed as $row)
                                    <tr>
                                        <td>{{ $row['name'] }}</td>
                                        <td>{{ $row['qty'] }}</td>
                                        <td>₱{{ number_format($row['cost'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </x-filament::section>
            </div>
        @endif
    </div>

    <style>
        .rpt { display: flex; flex-direction: column; gap: 1.25rem; }

        .rpt-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; }
        .rpt-presets { display: flex; flex-wrap: wrap; gap: 0.375rem; }
        .rpt-range { display: flex; flex-wrap: wrap; gap: 0.75rem; font-size: 0.75rem; color: rgb(107 114 128); align-items: center; }
        .dark .rpt-range { color: rgb(156 163 175); }
        .rpt-date-input {
            margin-left: 0.375rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            border: 1px solid rgba(0, 0, 0, 0.15);
            background: #fff;
            color: rgb(9 9 11);
            color-scheme: light;
            font-size: 0.813rem;
        }
        .dark .rpt-date-input { background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.15); color: rgb(250 250 250); color-scheme: dark; }

        .rpt-tabs-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            padding-bottom: 0.75rem;
        }
        .dark .rpt-tabs-row { border-color: rgba(255, 255, 255, 0.1); }
        .rpt-tabs { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .rpt-tab {
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            border: 1px solid rgba(0, 0, 0, 0.15);
            background: #fff;
            font-size: 0.813rem;
            font-weight: 600;
            color: rgb(107 114 128);
            cursor: pointer;
        }
        .dark .rpt-tab { background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.15); color: rgb(156 163 175); }
        .rpt-tab.is-active { background: rgb(8 145 178); border-color: rgb(8 145 178); color: #fff; }

        .rpt-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr)); gap: 0.75rem; }
        .rpt-stat {
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: #fff;
        }
        .dark .rpt-stat { background: rgb(17 24 39); border-color: rgba(255, 255, 255, 0.1); }
        .rpt-stat-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; color: rgb(107 114 128); }
        .dark .rpt-stat-label { color: rgb(156 163 175); }
        .rpt-stat-value { font-size: 1.5rem; font-weight: 700; color: rgb(9 9 11); margin-top: 0.25rem; }
        .dark .rpt-stat-value { color: rgb(250 250 250); }

        .rpt-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(20rem, 1fr)); gap: 1rem; }

        .rpt-table { width: 100%; border-collapse: collapse; font-size: 0.813rem; }
        .rpt-table th { text-align: left; padding: 0.5rem 0.625rem; color: rgb(107 114 128); font-weight: 600; border-bottom: 1px solid rgba(0, 0, 0, 0.1); }
        .dark .rpt-table th { color: rgb(156 163 175); border-color: rgba(255, 255, 255, 0.1); }
        .rpt-table td { padding: 0.5rem 0.625rem; border-bottom: 1px solid rgba(0, 0, 0, 0.05); color: rgb(9 9 11); }
        .dark .rpt-table td { border-color: rgba(255, 255, 255, 0.06); color: rgb(250 250 250); }
        .rpt-table tbody tr:last-child td { border-bottom: none; }

        .rpt-empty { font-size: 0.813rem; color: rgb(107 114 128); margin: 0; }
        .dark .rpt-empty { color: rgb(156 163 175); }
    </style>
</x-filament-panels::page>
