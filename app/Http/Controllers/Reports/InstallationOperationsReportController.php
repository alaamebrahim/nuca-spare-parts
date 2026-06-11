<?php

namespace App\Http\Controllers\Reports;

use App\Data\InstallationOperations\InstallationOperationsFilterData;
use App\Http\Controllers\Controller;
use App\Traits\InstallationOperationsBaseQueries;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallationOperationsReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = InstallationOperationsFilterData::from($this->filterParams($request));

        $records = InstallationOperationsBaseQueries::filtered($filters)
            ->orderByDesc('created_at')
            ->get();

        return view('exports.installation-operations-report', [
            'records' => $records,
        ]);
    }

    protected function filterParams(Request $request): array
    {
        return collect($request->only([
            'spare_part_id',
            'examine_city_id',
            'beneficiary_city_id',
            'status',
            'quantity_from',
            'quantity_to',
            'installation_date_from',
            'installation_date_to',
            'created_from',
            'created_to',
            'spare_part_type_id',
            'spare_part_category_id',
        ]))->reject(function ($value) {
            if (is_array($value)) {
                return empty($value);
            }

            return $value === null || $value === '';
        })->toArray();
    }
}
