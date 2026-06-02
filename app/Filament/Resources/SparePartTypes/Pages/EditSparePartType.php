<?php

namespace App\Filament\Resources\SparePartTypes\Pages;

use App\Filament\Resources\SparePartTypes\SparePartTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSparePartType extends EditRecord
{
    protected static string $resource = SparePartTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
