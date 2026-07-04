<?php

namespace App\Traits;

use App\Data\InstallationOperations\InstallationOperationsFilterData;
use App\Models\InstallationOperation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

trait InstallationOperationsBaseQueries
{
    public static function baseQuery(): Builder
    {
        return InstallationOperation::query()->with(['sparePart.type', 'sparePart.category', 'examineCity', 'beneficiaryCity']);
    }

    public static function filtered(InstallationOperationsFilterData $filters): Builder
    {
        $params = ['filter' => []];

        foreach (['spare_part_id', 'examine_city_id', 'beneficiary_city_id', 'spare_part_type_id', 'spare_part_category_id'] as $key) {
            if (! empty($filters->{$key})) {
                $params['filter'][$key] = $filters->{$key};
            }
        }

        foreach ([
            'quantity_from', 'quantity_to', 'installation_date_from', 'installation_date_to', 'created_from', 'created_to',
        ] as $key) {
            if (! empty($filters->{$key})) {
                $params['filter'][$key] = $filters->{$key};
            }
        }

        $request = Request::create('/', 'GET', $params);

        return QueryBuilder::for(self::baseQuery(), $request)
            ->allowedFilters([
                AllowedFilter::exact('spare_part_id'),
                AllowedFilter::exact('examine_city_id'),
                AllowedFilter::exact('beneficiary_city_id'),
                AllowedFilter::callback('quantity_from', function ($query, $value) {
                    $query->where('quantity', '>=', $value);
                }),
                AllowedFilter::callback('quantity_to', function ($query, $value) {
                    $query->where('quantity', '<=', $value);
                }),
                AllowedFilter::callback('installation_date_from', function ($query, $value) {
                    $query->whereDate('installation_date', '>=', $value);
                }),
                AllowedFilter::callback('installation_date_to', function ($query, $value) {
                    $query->whereDate('installation_date', '<=', $value);
                }),
                AllowedFilter::callback('created_from', function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }),
                AllowedFilter::callback('created_to', function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                }),
                AllowedFilter::callback('spare_part_type_id', function ($query, $value) {
                    $ids = is_array($value) ? $value : [$value];
                    $query->whereHas('sparePart', fn ($q) => $q->whereIn('type_id', $ids));
                }),
                AllowedFilter::callback('spare_part_category_id', function ($query, $value) {
                    $ids = is_array($value) ? $value : [$value];
                    $query->whereHas('sparePart', fn ($q) => $q->whereIn('category_id', $ids));
                }),
            ])
            ->getEloquentBuilder();
    }
}
