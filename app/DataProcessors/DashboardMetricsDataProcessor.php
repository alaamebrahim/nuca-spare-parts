<?php

namespace App\DataProcessors;

use App\Enums\InstallationStatusEnum;
use App\Enums\SparePartStatusEnum;
use App\Models\InstallationOperation;
use App\Models\SparePart;
use Illuminate\Support\Facades\DB;

class DashboardMetricsDataProcessor
{
    public static function noMaintenanceTotals(): array
    {
        $statuses = [
            SparePartStatusEnum::New->value,
            SparePartStatusEnum::UsedInGoodState->value,
            SparePartStatusEnum::Maintained->value,
        ];

        $row = SparePart::query()
            ->whereIn('spare_parts.status', $statuses)
            ->selectRaw('COALESCE(SUM(estimated_cost * quantity), 0) as purchase_total')
            ->selectRaw('COALESCE(SUM(COALESCE(maintenance_cost,0)), 0) as maintenance_total')
            ->first();

        $purchase = (float) ($row->purchase_total ?? 0);
        $maintenance = (float) ($row->maintenance_total ?? 0);

        return [
            'purchase_total' => $purchase,
            'maintenance_total' => $maintenance,
            'savings' => $purchase - $maintenance,
        ];
    }

    public static function installedTotals(): array
    {
        $row = InstallationOperation::query()
            ->join('spare_parts', 'installation_operations.spare_part_id', '=', 'spare_parts.id')
            ->selectRaw('COALESCE(SUM(spare_parts.estimated_cost * installation_operations.quantity), 0) as purchase_total')
            ->selectRaw('COALESCE(SUM(COALESCE(spare_parts.maintenance_cost,0)), 0) as maintenance_total')
            ->first();

        $purchase = (float) ($row->purchase_total ?? 0);
        $maintenance = (float) ($row->maintenance_total ?? 0);

        return [
            'purchase_total' => $purchase,
            'maintenance_total' => $maintenance,
            'savings' => $purchase - $maintenance,
        ];
    }

    public static function needsMaintenanceTotals(): array
    {
        $row = SparePart::query()
            ->where('spare_parts.status', SparePartStatusEnum::UsedNeedsMaintainance->value)
            ->selectRaw('COALESCE(SUM(estimated_cost * quantity), 0) as purchase_total')
            ->first();

        return [
            'purchase_total' => (float) ($row->purchase_total ?? 0),
        ];
    }
}