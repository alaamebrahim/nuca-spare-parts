<?php

namespace App\Filament\Resources\SparePartTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SparePartTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الحالة')
                    ->required(),
            ]);
    }
}
