<?php

namespace App\Traits;

use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;

trait HasPageExport
{
    protected function getExportAction(): Action
    {
        return Action::make('export')
            ->label('تصدير Excel')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->disabled(fn (): bool => ! $this->showResults)
            ->action(function () {
                $exportClass = $this->getExportClass();

                return Excel::download(
                    new $exportClass($this->getExportQuery()),
                    $this->getExportFilename()
                );
            });
    }

    protected function getExportFilename(): string
    {
        return $this->getExportBaseFilename().'-'.now()->format('Y-m-d').'.xlsx';
    }

    abstract protected function getExportClass(): string;

    abstract protected function getExportBaseFilename(): string;

    abstract protected function getExportQuery();
}
