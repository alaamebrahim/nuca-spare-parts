<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardClockWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-clock';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return true;
    }
}
