<?php

namespace App\Exports;

use App\Enums\SparePartStatusEnum;
use App\Models\City;
use App\Models\SparePartCategory;
use App\Models\SparePartType;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SparePartsImportLookupsSheet implements FromArray, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Lookups';
    }

    public function headings(): array
    {
        return [
            'cities',
            'types',
            'categories',
            'statuses',
        ];
    }

    public function array(): array
    {
        $cities = City::query()->orderBy('name')->pluck('name')->values();
        $types = SparePartType::query()->orderBy('name')->pluck('name')->values();
        $categories = SparePartCategory::query()->orderBy('name')->pluck('name')->values();
        $statuses = collect(SparePartStatusEnum::cases())->map(fn (SparePartStatusEnum $status) => $status->label())->values();

        $max = max($cities->count(), $types->count(), $categories->count(), $statuses->count(), 1);

        $rows = [];
        for ($i = 0; $i < $max; $i++) {
            $rows[] = [
                $cities->get($i),
                $types->get($i),
                $categories->get($i),
                $statuses->get($i),
            ];
        }

        return $rows;
    }
}

