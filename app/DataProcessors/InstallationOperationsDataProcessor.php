<?php

namespace App\DataProcessors;

use App\Models\SparePart;
use Illuminate\Support\Collection;

class InstallationOperationsDataProcessor
{
    public static function sparePartOptionsForCities(array $cityIds): Collection
    {
        $query = SparePart::query()->with(['type', 'category']);
        if (! empty($cityIds)) {
            $query->whereIn('city_id', $cityIds)->whereHas('installationOperations', function ($q) use ($cityIds) {
                $q->whereIn('examine_city_id', $cityIds);
            });
        }

        return $query->get()->mapWithKeys(function (SparePart $sparePart) {
            return [$sparePart->id => $sparePart->type->name.' - '.'الوصف الفني: '.$sparePart->technical_description];
        });
    }
}
