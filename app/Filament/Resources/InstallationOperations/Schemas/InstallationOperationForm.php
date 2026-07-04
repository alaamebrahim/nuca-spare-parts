<?php

namespace App\Filament\Resources\InstallationOperations\Schemas;

use App\Models\City;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard as ComponentsWizard;
use Filament\Schemas\Schema;

class InstallationOperationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ComponentsWizard::make([
                    ComponentsWizard\Step::make('تحديد مدينة الفحص')
                        ->schema([
                            Select::make('examine_city_id')
                                ->label('مدينة الفحص')
                                ->options(City::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                // Reset spare part when city changes to avoid invalid selection
                                ->afterStateUpdated(fn ($set) => $set('spare_part_id', null)),
                        ]),
                    ComponentsWizard\Step::make('تفاصيل العملية')
                        ->schema([
                            Select::make('spare_part_id')
                                ->label('قطعة الغيار')
                                ->options(function ($get) {
                                    $cityId = $get('examine_city_id');

                                    return \App\DataProcessors\SparePartsDataProcessor::optionsForCity($cityId);
                                })
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('beneficiary_city_id')
                                ->label('المدينة المستفيدة')
                                ->options(City::pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required(),
                            TextInput::make('quantity')
                                ->label('الكمية')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(fn ($get) => optional(\App\Models\SparePart::find($get('spare_part_id')))->available_quantity ?? null),
                            DatePicker::make('installation_date')
                                ->label('تاريخ التركيب')
                                ->required(),
                            Textarea::make('description')
                                ->label('كيفية الاستفادة')
                                ->rows(3),
                            Textarea::make('notes')
                                ->label('ملاحظات')
                                ->rows(3),
                        ]),
                ])->columnSpanFull(),
            ]);
    }
}
