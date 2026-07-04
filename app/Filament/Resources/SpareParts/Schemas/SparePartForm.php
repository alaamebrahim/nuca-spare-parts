<?php

namespace App\Filament\Resources\SpareParts\Schemas;

use App\Enums\SparePartStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SparePartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::components());
    }

    public static function components(
        string $locationField = 'location',
        string $technicalDescriptionField = 'technical_description',
    ): array {
        return [
            Section::make([
                Select::make('city_id')
                    ->label('المدينة التي تم الفحص بها')
                    ->searchable()
                    ->preload()
                    ->relationship('city', 'name'),
                Textarea::make($locationField)
                    ->label('مكان الفحص')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('type_id')
                    ->label('نوع المهمة')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship('type', 'name'),
                Textarea::make($technicalDescriptionField)
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
                    ->minValue(0)
                    ->default(0),
                TextInput::make('estimated_cost')
                    ->label('التكلفة التقديرية للوحدة')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                Select::make('status')
                    ->label('الحالة')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(SparePartStatusEnum::labels())
                    ->live()
                    ->afterStateUpdated(function ($state, $set): void {
                        if ($state !== SparePartStatusEnum::Maintained->value) {
                            $set('maintenance_cost', 0);
                            $set('maintenance_city_id', null);
                        }
                    }),
                TextInput::make('maintenance_cost')
                    ->label('تكلفة الصيانة ')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->visible(fn ($get): bool => $get('status') === SparePartStatusEnum::Maintained->value),
                Select::make('maintenance_city_id')
                    ->label('المدينة المنوطة بالصيانة')
                    ->searchable()
                    ->preload()
                    ->relationship('maintenanceCity', 'name')
                    ->visible(fn ($get): bool => $get('status') === SparePartStatusEnum::Maintained->value),
            ])->columnSpanFull(),
        ];
    }
}
