<?php

namespace App\DataProcessors;

use App\Models\SparePart;
use Illuminate\Support\Collection;

class SparePartsDataProcessor
{
    public static function labelForOption(SparePart $sparePart): string
    {
        return $sparePart->type->name.' - '.$sparePart->category->name.' (متاح: '.$sparePart->available_quantity.')';
    }

    public static function availableQuantity(SparePart $sparePart): int
    {
        return (int) $sparePart->available_quantity;
    }

    public static function optionsForCity(?int $cityId): Collection
    {
        $query = SparePart::query()->with(['type', 'category']);
        if ($cityId) {
            $query->where('city_id', $cityId);
        }
        return $query->get()->mapWithKeys(function (SparePart $sparePart) {
            return [$sparePart->id => self::labelForOption($sparePart)];
        });
    }

    public static function estimatedTotal(SparePart $sparePart): float|int
    {
        return $sparePart->quantity * $sparePart->estimated_cost;
    }

    public static function maintenanceTotal(SparePart $sparePart): float|int
    {
        return $sparePart->quantity * $sparePart->maintenance_cost;
    }
}

