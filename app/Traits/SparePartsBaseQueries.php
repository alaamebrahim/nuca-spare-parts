<?php

namespace App\Traits;

use App\Data\SpareParts\SparePartsReportFilterData;
use App\Models\SparePart;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

trait SparePartsBaseQueries
{
    public static function baseQuery(): Builder
    {
        return SparePart::query()->with(['city', 'type', 'category', 'maintenanceCity']);
    }

    public static function filtered(SparePartsReportFilterData $filters): Builder
    {
        $qb = QueryBuilder::for(self::baseQuery())
            ->allowedFilters([
                AllowedFilter::exact('city_id'),
                AllowedFilter::exact('type_id'),
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('maintenance_city_id'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback('quantity_from', function ($query, $value) {
                    $query->where('quantity', '>=', $value);
                }),
                AllowedFilter::callback('quantity_to', function ($query, $value) {
                    $query->where('quantity', '<=', $value);
                }),
                AllowedFilter::callback('cost_from', function ($query, $value) {
                    $query->where('estimated_cost', '>=', $value);
                }),
                AllowedFilter::callback('cost_to', function ($query, $value) {
                    $query->where('estimated_cost', '<=', $value);
                }),
                AllowedFilter::callback('created_from', function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }),
                AllowedFilter::callback('created_to', function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                }),
                AllowedFilter::callback('maintenance_cost_from', function ($query, $value) {
                    $query->where('maintenance_cost', '>=', $value);
                }),
                AllowedFilter::callback('maintenance_cost_to', function ($query, $value) {
                    $query->where('maintenance_cost', '<=', $value);
                }),
            ]);

        $params = [];
        $params['filter'] = [];
        foreach (['city_id','type_id','category_id','maintenance_city_id','status'] as $key) {
            if (!empty($filters->{$key})) {
                $params['filter'][$key] = $filters->{$key};
            }
        }
        foreach ([
            'quantity_from','quantity_to','cost_from','cost_to',
            'created_from','created_to','maintenance_cost_from','maintenance_cost_to',
        ] as $key) {
            if (!empty($filters->{$key})) {
                $params['filter'][$key] = $filters->{$key};
            }
        }

        return $qb->setRequest(request()->merge($params))->getEloquentBuilder();
    }
}

