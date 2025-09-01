<?php

namespace App\Filament\Resources\DocumentTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DocumentTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required(),
            ]);
    }
}
