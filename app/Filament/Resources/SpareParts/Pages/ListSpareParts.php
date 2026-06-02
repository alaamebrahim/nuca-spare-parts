<?php

namespace App\Filament\Resources\SpareParts\Pages;

use App\Filament\Resources\SpareParts\SparePartResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpareParts extends ListRecords
{
    protected static string $resource = SparePartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('massImport')
                ->label('استيراد (Excel)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->url(fn (): string => SparePartResource::getUrl('import')),
        ];
    }
}
