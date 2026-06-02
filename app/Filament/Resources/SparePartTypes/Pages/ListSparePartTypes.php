<?php

namespace App\Filament\Resources\SparePartTypes\Pages;

use App\Filament\Resources\SparePartTypes\SparePartTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSparePartTypes extends ListRecords
{
    protected static string $resource = SparePartTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
