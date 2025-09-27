<?php

namespace App\Filament\Resources\SpareParts\Tables;

use App\Enums\SparePartStatusEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SparePartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type.name')
                    ->alignCenter()
                    ->label('نوع المهمة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('الفئة')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
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
