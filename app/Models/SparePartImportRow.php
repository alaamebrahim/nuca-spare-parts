<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparePartImportRow extends Model
{
    protected $fillable = [
        'batch_id',
        'city_name_raw',
        'type_name_raw',
        'category_name_raw',
        'maintenance_city_name_raw',
        'location_raw',
        'technical_description_raw',
        'quantity_raw',
        'status_raw',
        'estimated_cost_raw',
        'maintenance_cost_raw',
        'city_id',
        'type_id',
        'category_id',
        'maintenance_city_id',
        'quantity',
        'estimated_cost',
        'maintenance_cost',
        'status',
        'has_errors',
        'errors',
    ];

    protected $casts = [
        'has_errors' => 'bool',
        'errors' => 'array',
        'estimated_cost' => 'decimal:2',
        'maintenance_cost' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SparePartImportBatch::class, 'batch_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(SparePartType::class, 'type_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SparePartCategory::class, 'category_id');
    }

    public function maintenanceCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'maintenance_city_id');
    }
}
