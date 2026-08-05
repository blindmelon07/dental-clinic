<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;

class Invoice extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number', 'clinic_id', 'patient_id', 'appointment_id', 'dental_record_id', 'status',
        'invoice_date', 'due_date', 'subtotal', 'discount_amount', 'discount_type',
        'tax_rate', 'tax_amount', 'total', 'amount_paid', 'balance_due', 'notes', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status'          => InvoiceStatus::class,
            'invoice_date'    => 'date',
            'due_date'        => 'date',
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_rate'        => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total'           => 'decimal:2',
            'amount_paid'     => 'decimal:2',
            'balance_due'     => 'decimal:2',
            'paid_at'         => 'datetime',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function dentalRecord(): BelongsTo
    {
        return $this->belongsTo(DentalRecord::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function recalculate(): void
    {
        $subtotal = $this->items()->sum('total');
        $taxAmount = $subtotal * ($this->tax_rate / 100);
        $total = $subtotal - $this->discount_amount + $taxAmount;
        $amountPaid = $this->payments()->sum('amount');

        $this->update([
            'subtotal'     => $subtotal,
            'tax_amount'   => $taxAmount,
            'total'        => $total,
            'amount_paid'  => $amountPaid,
            'balance_due'  => max(0, $total - $amountPaid),
        ]);
    }

    public static function generateNumber(): string
    {
        $prefix = 'INV-' . date('Ymd') . '-';

        // Base the next sequence on the highest existing suffix for today (including
        // soft-deleted invoices, since their invoice_number still occupies the unique
        // slot). Counting non-deleted rows undercounts and produces collisions.
        $lastSequence = static::withTrashed()
            ->where('invoice_number', 'like', $prefix . '%')
            ->pluck('invoice_number')
            ->map(fn (string $number) => (int) substr($number, strlen($prefix)))
            ->max();

        return $prefix . str_pad((int) $lastSequence + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create an invoice with a freshly generated invoice_number, retrying on a
     * duplicate-key collision (e.g. two requests generating a number at the same
     * instant) rather than letting the request fail outright.
     */
    public static function createUnique(array $attributes): static
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return static::create(array_merge($attributes, [
                    'invoice_number' => static::generateNumber(),
                ]));
            } catch (QueryException $e) {
                $isDuplicateInvoiceNumber = (int) $e->getCode() === 23000
                    && str_contains($e->getMessage(), 'invoices_invoice_number_unique');

                if (! $isDuplicateInvoiceNumber || $attempt >= 5) {
                    throw $e;
                }
            }
        }
    }
}
