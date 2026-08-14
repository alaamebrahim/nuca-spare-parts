<?php

namespace App\Traits;

use App\Data\SpareParts\SparePartsReportFilterData;
use App\Enums\SparePartStatusEnum;
use App\Models\SparePart;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

trait SparePartsBaseQueries
{
    public static function baseQuery(): Builder
    {
        return SparePart::query()->with(['city', 'type', 'category', 'maintenanceCity']);
    }

    public static function applySearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        $term = '%'.$search.'%';

        return $query->where(function (Builder $query) use ($search, $term): void {
            $query
                ->where('spare_parts.technical_description', 'like', $term)
                ->orWhere('spare_parts.location', 'like', $term)
                ->orWhere('spare_parts.quantity', 'like', $term)
                ->orWhere('spare_parts.status', 'like', $term)
                ->orWhere('spare_parts.estimated_cost', 'like', $term)
                ->orWhere('spare_parts.maintenance_cost', 'like', $term)
                ->orWhereRelation('city', 'name', 'like', $term)
                ->orWhereRelation('type', 'name', 'like', $term)
                ->orWhereRelation('category', 'name', 'like', $term)
                ->orWhereRelation('maintenanceCity', 'name', 'like', $term);

            $matchingStatuses = array_values(array_filter(
                SparePartStatusEnum::cases(),
                fn (SparePartStatusEnum $status): bool => str_contains($status->label(), $search)
                    || str_contains(mb_strtolower($status->value), mb_strtolower($search)),
            ));

            if ($matchingStatuses !== []) {
                $query->orWhereIn(
                    'spare_parts.status',
                    array_map(fn (SparePartStatusEnum $status): string => $status->value, $matchingStatuses),
                );
            }
        });
    }

    public static function filtered(SparePartsReportFilterData $filters): Builder
    {
        $params = ['filter' => []];

        foreach (['city_id', 'type_id', 'category_id', 'maintenance_city_id', 'status'] as $key) {
            if (! empty($filters->{$key})) {
                $params['filter'][$key] = $filters->{$key};
            }
        }

        foreach ([
            'quantity_from', 'quantity_to', 'cost_from', 'cost_to',
            'created_from', 'created_to', 'maintenance_cost_from', 'maintenance_cost_to',
        ] as $key) {
            if (! empty($filters->{$key})) {
                $params['filter'][$key] = $filters->{$key};
            }
        }

        $request = Request::create('/', 'GET', $params);

        return QueryBuilder::for(self::baseQuery(), $request)
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
            ])
            ->getEloquentBuilder();
    }
}
