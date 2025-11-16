<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\SparePart;

class DashboardOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-overview';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        // Sum total estimated purchase cost: unit cost * quantity
        $purchaseTotal = SparePart::query()
            ->sum(DB::raw('estimated_cost * quantity'));

        // Sum total maintenance cost: unit maintenance cost * quantity
        $maintenanceTotal = SparePart::query()
            ->sum(DB::raw('maintenance_cost * quantity'));

        $savings = $purchaseTotal - $maintenanceTotal;

        $now = Carbon::now();

        return compact('purchaseTotal', 'maintenanceTotal', 'savings', 'now');
    }
}
