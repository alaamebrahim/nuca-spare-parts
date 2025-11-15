<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use App\Models\SparePart;

class DashboardOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-overview';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $purchaseTotal = SparePart::query()
            ->sum('estimated_cost');

        $maintenanceTotal = SparePart::query()
            ->sum('maintenance_cost');

        $savings = $purchaseTotal - $maintenanceTotal;

        $now = Carbon::now();

        return compact('purchaseTotal', 'maintenanceTotal', 'savings', 'now');
    }
}
