<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice_id', 'service_id', 'description', 'quantity', 'unit_price', 'total',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total'      => 'decimal:2',
            'quantity'   => 'integer',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
