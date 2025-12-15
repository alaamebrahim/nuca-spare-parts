<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use App\DataProcessors\DashboardMetricsDataProcessor;

class DashboardOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-overview';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $now = Carbon::now();

        return [
            'noMaintenance' => DashboardMetricsDataProcessor::noMaintenanceTotals(),
            'installed' => DashboardMetricsDataProcessor::installedTotals(),
            'needsMaintenance' => DashboardMetricsDataProcessor::needsMaintenanceTotals(),
            'now' => $now,
        ];
    }
}