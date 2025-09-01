<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    Select::make('city_id')
                        ->label('المدينة')
                        ->preload()
                        ->searchable()
                        ->relationship('city', 'name')
                        ->required(),
                    Select::make('document_type_id')
                        ->label('نوع الملف')
                        ->preload()
                        ->searchable()
                        ->relationship('documentType', 'name')
                        ->required(),
                    DatePicker::make('document_date')
                        ->label('تاريخ الملف'),
                    Textarea::make('description')
                        ->default(null)
                        ->label('الوصف')
                        ->required()
                        ->columnSpanFull(),
                    FileUpload::make('file')
                        ->label('الملف')
                        ->directory('documents/' . auth()->id() . '/' . now()->year . '/' . now()->month)
                        ->visibility('public')
                        ->required(),
                    Textarea::make('notes')
                        ->label('الملاحظات')
                        ->default(null)
                        ->columnSpanFull(),
                ])
                    ->columns(1)->columnSpanFull()
            ]);
    }
}
