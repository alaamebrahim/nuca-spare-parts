<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallationOperation extends Model
{
    protected $fillable = [
        'spare_part_id',
        'examine_city_id',
        'beneficiary_city_id',
        'quantity',
        'installation_date',
        'description',
        'notes',
    ];

    protected $casts = [
        'installation_date' => 'date',
    ];

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function examineCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'examine_city_id');
    }

    public function beneficiaryCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'beneficiary_city_id');
    }
}
