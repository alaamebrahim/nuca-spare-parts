<?php

namespace App\Filament\Resources\SparePartCategories\Pages;

use App\Filament\Resources\SparePartCategories\SparePartCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSparePartCategory extends EditRecord
{
    protected static string $resource = SparePartCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
