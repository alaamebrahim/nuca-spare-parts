<?php

namespace App\Filament\Widgets;

use App\DataProcessors\DashboardMetricsDataProcessor;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class DashboardOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-overview';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 12,
        'xl' => 8,
    ];

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
