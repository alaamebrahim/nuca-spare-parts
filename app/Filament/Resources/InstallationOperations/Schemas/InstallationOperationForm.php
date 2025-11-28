<?php

namespace App\Filament\Resources\InstallationOperations\Schemas;

use App\Enums\InstallationStatusEnum;
use App\Models\City;
use App\Models\SparePart;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard as ComponentsWizard;

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
                                ->afterStateUpdated(fn($set) => $set('spare_part_id', null)),
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
                                ->maxValue(fn($get) => optional(\App\Models\SparePart::find($get('spare_part_id')))->available_quantity ?? null),
                            DatePicker::make('installation_date')
                                ->label('تاريخ التركيب')
                                ->required(),
                            Select::make('status')
                                ->label('الحالة')
                                ->options(InstallationStatusEnum::labels())
                                ->required()
                                ->default(InstallationStatusEnum::Pending->value),
                            Textarea::make('description')
                                ->label('كيفية الاستفادة')
                                ->rows(3),
                            Textarea::make('notes')
                                ->label('ملاحظات')
                                ->rows(3),
                        ]),
                ])->columnSpanFull()
            ]);
    }
}
