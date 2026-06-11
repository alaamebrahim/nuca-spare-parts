<?php

namespace App\Http\Controllers\Reports;

use App\Data\SpareParts\SparePartsReportFilterData;
use App\Http\Controllers\Controller;
use App\Traits\SparePartsBaseQueries;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SparePartsReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = SparePartsReportFilterData::from($this->filterParams($request));

        $records = SparePartsBaseQueries::filtered($filters)
            ->orderByDesc('created_at')
            ->get();

        return view('exports.spare-parts-report', [
            'records' => $records,
        ]);
    }

    protected function filterParams(Request $request): array
    {
        return collect($request->only([
            'city_id',
            'type_id',
            'category_id',
            'status',
            'quantity_from',
            'quantity_to',
            'cost_from',
            'cost_to',
            'created_from',
            'created_to',
            'maintenance_city_id',
            'maintenance_cost_from',
            'maintenance_cost_to',
        ]))->reject(function ($value) {
            if (is_array($value)) {
                return empty($value);
            }

            return $value === null || $value === '';
        })->toArray();
    }
}
