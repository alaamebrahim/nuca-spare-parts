<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Livewire;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;
use Illuminate\Contracts\View\View;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'xl' => 12,
        ];
    }

    public function getPageClasses(): array
    {
        return ['fi-dashboard-page'];
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.dashboard-header');
    }

    /**
     * @param  array<class-string<Widget> | WidgetConfiguration>  $widgets
     * @param  array<string, mixed>  $data
     * @return array<Livewire>
     */
    public function getWidgetsSchemaComponents(array $widgets, array $data = []): array
    {
        return collect($widgets)
            ->values()
            ->filter(fn (string|WidgetConfiguration $widget): bool => $this->normalizeWidgetClass($widget)::canView())
            ->map(function (string|WidgetConfiguration $widget, int $widgetKey) use ($data): Livewire {
                $widgetClass = $this->normalizeWidgetClass($widget);

                return Livewire::make(
                    $widgetClass,
                    fn (): array => [
                        ...$this->getWidgetData(),
                        ...$data,
                        ...(($widget instanceof WidgetConfiguration) ? [
                            ...$widget->widget::getDefaultProperties(),
                            ...$widget->getProperties(),
                        ] : $widgetClass::getDefaultProperties()),
                    ],
                )
                    ->key("{$widgetClass}-{$widgetKey}")
                    ->columnSpan($this->widgetColumnSpan($widgetClass));
            })
            ->all();
    }

    /**
     * @param  class-string<Widget>  $widgetClass
     * @return int|string|array<string, int|string|null>
     */
    private function widgetColumnSpan(string $widgetClass): int|string|array
    {
        return match ($widgetClass) {
            \App\Filament\Widgets\DashboardOverviewWidget::class => [
                'default' => 'full',
                'xl' => 8,
            ],
            \App\Filament\Widgets\DashboardCityCountsWidget::class => [
                'default' => 'full',
                'xl' => 4,
            ],
            default => 'full',
        };
    }
}
