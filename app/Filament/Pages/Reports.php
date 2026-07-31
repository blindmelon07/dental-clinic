<?php

namespace App\Filament\Pages;

use App\Enums\AppointmentStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Appointment;
use App\Models\DentalRecord;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\MedicineDispensing;
use App\Models\Patient;
use App\Models\Payment;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reports';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.reports';

    public string $startDate;
    public string $endDate;
    public string $activeTab = 'revenue';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole(['super_admin', 'admin']) ?? false;
    }

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function applyPreset(string $preset): void
    {
        [$start, $end] = match ($preset) {
            'today' => [now(), now()],
            'this_week' => [now()->startOfWeek(), now()],
            'this_month' => [now()->startOfMonth(), now()],
            'this_year' => [now()->startOfYear(), now()],
            'all_time' => [Carbon::createFromDate(2000, 1, 1), now()],
            default => [now()->startOfMonth(), now()],
        };

        $this->startDate = $start->format('Y-m-d');
        $this->endDate = $end->format('Y-m-d');
    }

    /**
     * Full-day Carbon bounds, for comparing against DATETIME/TIMESTAMP columns.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function dateTimeRange(): array
    {
        return [Carbon::parse($this->startDate)->startOfDay(), Carbon::parse($this->endDate)->endOfDay()];
    }

    /**
     * Constrains a DATE-semantic column to the selected range using whereDate(),
     * since Eloquent's 'date' cast does not strip time-of-day before saving —
     * a plain whereBetween() with 'Y-m-d' strings can miss same-day records.
     */
    protected function whereInDateRange(Builder $query, string $column): Builder
    {
        return $query
            ->whereDate($column, '>=', $this->startDate)
            ->whereDate($column, '<=', $this->endDate);
    }

    // ── Revenue ──────────────────────────────────────────────────────────

    public function getRevenueSummary(): array
    {
        [$start, $end] = $this->dateTimeRange();

        return [
            'totalCollected' => Payment::whereBetween('paid_at', [$start, $end])->sum('amount'),
            'totalInvoiced' => $this->whereInDateRange(Invoice::query(), 'invoice_date')->sum('total'),
            'paymentsCount' => Payment::whereBetween('paid_at', [$start, $end])->count(),
            'outstanding' => Invoice::whereNotIn('status', [InvoiceStatus::Paid, InvoiceStatus::Cancelled])->sum('balance_due'),
        ];
    }

    public function getRevenueByMethod(): Collection
    {
        [$start, $end] = $this->dateTimeRange();

        return Payment::whereBetween('paid_at', [$start, $end])
            ->get()
            ->groupBy('payment_method')
            ->map(fn (Collection $payments, string $method) => [
                'label' => PaymentMethod::from($method)->label(),
                'count' => $payments->count(),
                'total' => $payments->sum('amount'),
            ])
            ->sortByDesc('total')
            ->values();
    }

    public function getRevenueByMonth(): Collection
    {
        [$start, $end] = $this->dateTimeRange();

        return Payment::whereBetween('paid_at', [$start, $end])
            ->get()
            ->groupBy(fn (Payment $payment) => $payment->paid_at->format('Y-m'))
            ->map(fn (Collection $payments, string $month) => [
                'label' => Carbon::createFromFormat('Y-m', $month)->format('M Y'),
                'total' => $payments->sum('amount'),
                'count' => $payments->count(),
            ])
            ->sortKeys()
            ->values();
    }

    public function getRecentInvoices(?int $limit = 15): Collection
    {
        return $this->whereInDateRange(Invoice::query(), 'invoice_date')
            ->with('patient')
            ->latest('invoice_date')
            ->when($limit, fn (Builder $query) => $query->limit($limit))
            ->get();
    }

    // ── Appointments ─────────────────────────────────────────────────────

    public function getAppointmentSummary(): array
    {
        $appointments = $this->whereInDateRange(Appointment::query(), 'appointment_date')->get();

        $byStatus = $appointments
            ->groupBy(fn (Appointment $a) => $a->status->value)
            ->map(fn (Collection $group, string $status) => [
                'label' => AppointmentStatus::from($status)->label(),
                'color' => AppointmentStatus::from($status)->color(),
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values();

        return [
            'total' => $appointments->count(),
            'byStatus' => $byStatus,
        ];
    }

    public function getAppointmentsByDentist(): Collection
    {
        return $this->whereInDateRange(Appointment::query(), 'appointment_date')
            ->with('dentist.user')
            ->get()
            ->groupBy('dentist_id')
            ->map(fn (Collection $group) => [
                'name' => $group->first()->dentist?->user?->name ?? 'Unassigned',
                'count' => $group->count(),
                'completed' => $group->where('status', AppointmentStatus::Completed)->count(),
            ])
            ->sortByDesc('count')
            ->values();
    }

    // ── Patients & Dental Records ────────────────────────────────────────

    public function getPatientRecordSummary(): array
    {
        [$start, $end] = $this->dateTimeRange();

        return [
            'newPatients' => Patient::whereBetween('created_at', [$start, $end])->count(),
            'visits' => $this->whereInDateRange(DentalRecord::query(), 'visit_date')->count(),
        ];
    }

    public function getTopDiagnoses(): Collection
    {
        return $this->whereInDateRange(DentalRecord::query(), 'visit_date')
            ->get()
            ->flatMap(fn (DentalRecord $record) => $record->diagnosisNames())
            ->countBy()
            ->sortDesc()
            ->take(10)
            ->map(fn (int $count, string $name) => ['name' => $name, 'count' => $count])
            ->values();
    }

    // ── Inventory ────────────────────────────────────────────────────────

    public function getInventorySummary(): array
    {
        [$start, $end] = $this->dateTimeRange();

        $dispensings = MedicineDispensing::whereBetween('dispensed_at', [$start, $end])->get();

        return [
            'lowStock' => Medicine::where('is_active', true)->whereColumn('current_stock', '<=', 'minimum_stock')->count(),
            'dispensedQty' => $dispensings->sum('quantity'),
            'dispensedCost' => $dispensings->sum(fn (MedicineDispensing $d) => $d->quantity * $d->unit_price),
        ];
    }

    public function getLowStockMedicines(?int $limit = 15): Collection
    {
        return Medicine::where('is_active', true)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->orderBy('current_stock')
            ->when($limit, fn (Builder $query) => $query->limit($limit))
            ->get();
    }

    public function getTopDispensedMedicines(): Collection
    {
        [$start, $end] = $this->dateTimeRange();

        return MedicineDispensing::whereBetween('dispensed_at', [$start, $end])
            ->with('medicine')
            ->get()
            ->groupBy('medicine_id')
            ->map(fn (Collection $group) => [
                'name' => $group->first()->medicine?->display_name ?? 'Unknown',
                'qty' => $group->sum('quantity'),
                'cost' => $group->sum(fn (MedicineDispensing $d) => $d->quantity * $d->unit_price),
            ])
            ->sortByDesc('qty')
            ->take(10)
            ->values();
    }

    // ── CSV export ───────────────────────────────────────────────────────

    /**
     * @param  array<int, array{0: string, 1: array<int, string>, 2: array<int, array<int, mixed>>}>  $sections
     *         Each entry is [section title, column headers, rows].
     */
    protected function csvResponse(string $filename, array $sections): StreamedResponse
    {
        return response()->streamDownload(function () use ($sections) {
            $out = fopen('php://output', 'w');

            foreach ($sections as [$title, $headers, $rows]) {
                fputcsv($out, [$title]);
                fputcsv($out, $headers);

                foreach ($rows as $row) {
                    fputcsv($out, $row);
                }

                fputcsv($out, []);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function rangeSuffix(): string
    {
        return "{$this->startDate}_to_{$this->endDate}";
    }

    public function exportRevenue(): StreamedResponse
    {
        $summary = $this->getRevenueSummary();

        return $this->csvResponse("revenue-report-{$this->rangeSuffix()}.csv", [
            ['Summary', ['Metric', 'Value'], [
                ['Total Collected', $summary['totalCollected']],
                ['Total Invoiced', $summary['totalInvoiced']],
                ['Payments Recorded', $summary['paymentsCount']],
                ['Outstanding Balance', $summary['outstanding']],
            ]],
            ['Revenue by Month', ['Month', 'Payments', 'Total'], $this->getRevenueByMonth()
                ->map(fn (array $r) => [$r['label'], $r['count'], $r['total']])->all()],
            ['Revenue by Payment Method', ['Method', 'Count', 'Total'], $this->getRevenueByMethod()
                ->map(fn (array $r) => [$r['label'], $r['count'], $r['total']])->all()],
            ['Invoices', ['Invoice #', 'Patient', 'Date', 'Status', 'Total'], $this->getRecentInvoices(null)
                ->map(fn (Invoice $invoice) => [
                    $invoice->invoice_number,
                    $invoice->patient?->full_name ?? '—',
                    $invoice->invoice_date->format('Y-m-d'),
                    $invoice->status->label(),
                    $invoice->total,
                ])->all()],
        ]);
    }

    public function exportAppointments(): StreamedResponse
    {
        $summary = $this->getAppointmentSummary();

        return $this->csvResponse("appointments-report-{$this->rangeSuffix()}.csv", [
            ['Summary', ['Metric', 'Value'], [
                ['Total Appointments', $summary['total']],
            ]],
            ['By Status', ['Status', 'Count'], $summary['byStatus']
                ->map(fn (array $r) => [$r['label'], $r['count']])->all()],
            ['By Dentist', ['Dentist', 'Total', 'Completed'], $this->getAppointmentsByDentist()
                ->map(fn (array $r) => [$r['name'], $r['count'], $r['completed']])->all()],
        ]);
    }

    public function exportPatients(): StreamedResponse
    {
        $summary = $this->getPatientRecordSummary();

        return $this->csvResponse("patients-report-{$this->rangeSuffix()}.csv", [
            ['Summary', ['Metric', 'Value'], [
                ['New Patients', $summary['newPatients']],
                ['Dental Visits', $summary['visits']],
            ]],
            ['Top Diagnoses', ['Diagnosis', 'Occurrences'], $this->getTopDiagnoses()
                ->map(fn (array $r) => [$r['name'], $r['count']])->all()],
        ]);
    }

    public function exportInventory(): StreamedResponse
    {
        $summary = $this->getInventorySummary();

        return $this->csvResponse("inventory-report-{$this->rangeSuffix()}.csv", [
            ['Summary', ['Metric', 'Value'], [
                ['Low Stock Medicines', $summary['lowStock']],
                ['Dispensed Quantity', $summary['dispensedQty']],
                ['Dispensed Cost', $summary['dispensedCost']],
            ]],
            ['Low Stock Medicines', ['Medicine', 'Stock', 'Unit', 'Minimum'], $this->getLowStockMedicines(null)
                ->map(fn (Medicine $medicine) => [
                    $medicine->display_name, $medicine->current_stock, $medicine->unit, $medicine->minimum_stock,
                ])->all()],
            ['Top Dispensed Medicines', ['Medicine', 'Quantity', 'Cost'], $this->getTopDispensedMedicines()
                ->map(fn (array $r) => [$r['name'], $r['qty'], $r['cost']])->all()],
        ]);
    }
}
