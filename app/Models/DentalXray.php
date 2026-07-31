<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentalXray extends Model
{
    use Auditable;

    protected $fillable = [
        'dental_record_id', 'file_path', 'xray_type', 'findings',
    ];

    public function dentalRecord(): BelongsTo
    {
        return $this->belongsTo(DentalRecord::class);
    }
}
