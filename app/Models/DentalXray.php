<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DentalXray extends Model
{
    protected $fillable = [
        'dental_record_id', 'file_path', 'xray_type', 'findings',
    ];

    public function dentalRecord(): BelongsTo
    {
        return $this->belongsTo(DentalRecord::class);
    }
}
