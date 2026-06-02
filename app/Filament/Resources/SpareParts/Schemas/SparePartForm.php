<?php

namespace App\Filament\Resources\SpareParts\Schemas;

use App\Enums\SparePartStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class SparePartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    Select::make('city_id')
                        ->label('المدينة التي تم الفحص بها')
                        ->searchable()
                        ->preload()
                        ->relationship('city', 'name'),
                    Textarea::make('location')
                        ->label('مكان الفحص')
                        ->default(null)
                        ->columnSpanFull(),
                    Select::make('type_id')
                        ->label('نوع المهمة')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship('type', 'name'),
                    Textarea::make('technical_description')
                        ->label('الوصف الفني')
                        ->default(null)
                        ->columnSpanFull(),
                    Select::make('category_id')
                        ->label('الفئة')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship('category', 'name'),
                    TextInput::make('quantity')
                        ->label('الكمية')
                        ->required()
                        ->numeric()
                        ->default(0),
                    TextInput::make('estimated_cost')
                        ->label('التكلفة التقديرية للوحدة')
                        ->numeric()
                        ->default(0.0),
                    Select::make('status')
                        ->label('الحالة')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->options(SparePartStatusEnum::labels())
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state !== 'Maintained') {
                                $set('maintenance_cost', 0.0);
                                $set('maintenance_city_id', null);
                            }
                        }),
                    TextInput::make('maintenance_cost')
                        ->label('تكلفة الصيانة ')
                        ->numeric()
                        ->default(0.0)
                        ->visible(fn($get) => $get('status') === 'Maintained'),
                    Select::make('maintenance_city_id')
                        ->label('المدينة المنوطة بالصيانة')
                        ->searchable()
                        ->preload()
                        ->relationship('maintenanceCity', 'name')
                        ->visible(fn($get) => $get('status') === 'Maintained'),
                ])->columnSpanFull()
            ]);
    }
}
