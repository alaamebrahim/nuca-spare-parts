<?php

namespace App\Filament\Resources\SpareParts\Tables;

use App\Enums\SparePartStatusEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\City;
use Illuminate\Database\Eloquent\Builder;

class SparePartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withInstallationQuantities())
            ->columns([
                TextColumn::make('type.name')
                    ->alignCenter()
                    ->label('نوع المهمة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('technical_description')
                    ->label('الوصف الفني')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('category.name')
                    ->label('الفئة')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('الكمية الإجمالية')
                    ->numeric()
                    ->color('info')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('installed_quantity')
                    ->label('الكمية المنقولة')
                    ->numeric()
                    ->color('success')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('available_quantity')
                    ->label('الكمية المتاحة')
                    ->numeric()
                    ->color('danger')
                    ->alignCenter()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw(
                            '(quantity - COALESCE(installed_quantity, 0)) '.$direction
                        );
                    }),
                TextColumn::make('status')
                    ->formatStateUsing(fn($state) => SparePartStatusEnum::from($state)->label())
                    ->alignCenter()
                    ->badge()
                    ->label('الحالة')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('city.name')
                    ->label('المدينة التي تم الفحص بها')
                    ->alignCenter()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estimated_cost')
                    ->label('التكلفة التقديرية للوحدة')
                    ->alignCenter()
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('maintenance_cost')
                    ->label('تكلفة الصيانة')
                    ->alignCenter()
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('تاريخ الاضافة')
                    ->alignCenter()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->alignCenter()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(SparePartStatusEnum::labels())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type_id')
                    ->label('نوع المهمة')
                    ->relationship('type', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category_id')
                    ->label('الفئة')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('city_id')
                    ->label('المدينة التي تم الفحص بها')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('maintenance_city_id')
                    ->label('المدينة المنوطة بالصيانة')
                    ->relationship('maintenanceCity', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('عرض التفاصيل')
                        ->modalHeading('تفاصيل قطع الغيار')
                        ->modalContent(fn($record) => view('filament.resources.spare-parts.modals.details', [
                            'record' => $record
                        ]))
                        ->modalWidth('4xl')
                        ->schema([])
                        ->fillForm([]),
                    Action::make('install')
                        ->label('نقل وتركيب')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->color('success')
                        ->visible(fn($record) => $record->available_quantity > 0)
                        ->form([
                            TextInput::make('spare_part_info')
                                ->label('معلومات القطعة')
                                ->disabled()
                                ->default(fn($record) => "نوع: {$record->type->name} | الفئة: {$record->category->name} | الكمية المتاحة: {$record->available_quantity}"),
                            Select::make('examine_city_id')
                                ->label('مدينة الفحص')
                                ->options(City::pluck('name', 'id'))
                                ->default(fn($record) => $record->city_id)
                                ->disabled()
                                ->dehydrated(false)
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
                                ->maxValue(fn($record) => $record->available_quantity),
                            DatePicker::make('installation_date')
                                ->label('تاريخ التركيب')
                                ->required(),
                            Textarea::make('description')
                                ->label('كيفية الاستفادة')
                                ->rows(3),
                            Textarea::make('notes')
                                ->label('ملاحظات')
                                ->rows(3),
                        ])
                        ->action(function (array $data, $record) {
                            $input = \App\Data\InstallationOperations\CreateInstallationOperationInputData::from([
                                'spare_part_id' => $record->id,
                                'beneficiary_city_id' => $data['beneficiary_city_id'],
                                'quantity' => $data['quantity'],
                                'installation_date' => $data['installation_date'],
                                'description' => $data['description'] ?? null,
                                'notes' => $data['notes'] ?? null,
                            ]);
                            \App\Actions\CreateInstallationOperationAction::run($input);
                        })
                        ->modalHeading('نقل وتركيب قطع الغيار')
                        ->modalWidth('2xl'),
                    EditAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
