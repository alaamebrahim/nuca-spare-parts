<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparePart extends Model
{
    //

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(SparePartType::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SparePartCategory::class);
    }

    public function maintenanceCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'maintenance_city_id');
    }

    public function installationOperations(): HasMany
    {
        return $this->hasMany(InstallationOperation::class);
    }

    public function getAvailableQuantityAttribute(): int
    {
        $installedQuantity = $this->installationOperations()
            ->where('status', '!=', 'cancelled')
            ->sum('quantity');

        return max(0, $this->quantity - $installedQuantity);
    }
}
