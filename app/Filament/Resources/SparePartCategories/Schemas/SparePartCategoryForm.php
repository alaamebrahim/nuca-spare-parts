<?php

namespace App\Filament\Resources\SparePartCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SparePartCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الفئة')
                    ->required(),
            ]);
    }
}
