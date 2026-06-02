<?php

namespace App\Filament\Resources\SparePartCategories\Pages;

use App\Filament\Resources\SparePartCategories\SparePartCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSparePartCategories extends ListRecords
{
    protected static string $resource = SparePartCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
