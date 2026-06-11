<?php

namespace App\Traits;

use Filament\Actions\Action;

trait HasReportPrintExport
{
    protected function getReportFilterParams(): array
    {
        return collect($this->data ?? [])->reject(function ($value) {
            if (is_array($value)) {
                return empty($value);
            }

            return $value === null || $value === '';
        })->toArray();
    }

    protected function getPrintReportAction(string $routeName): Action
    {
        return Action::make('print_report')
            ->label('طباعة / PDF')
            ->icon('heroicon-o-printer')
            ->color('info')
            ->disabled(fn (): bool => ! $this->showResults)
            ->url(fn (): string => route($routeName, $this->getReportFilterParams()))
            ->openUrlInNewTab();
    }
}
