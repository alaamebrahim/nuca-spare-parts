<?php

namespace App\Filament\Resources\InstallationOperations\Tables;

use App\Enums\InstallationStatusEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InstallationOperationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sparePart.type.name')
                    ->label('نوع القطعة')
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sparePart.category.name')
                    ->label('فئة القطعة')
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('examineCity.name')
                    ->label('مدينة الفحص')
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('beneficiaryCity.name')
                    ->label('المدينة المستفيدة')
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('installation_date')
                    ->label('تاريخ التركيب')
                    ->date()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('status')
                    ->formatStateUsing(fn($state) => InstallationStatusEnum::from($state)->label())
                    ->alignCenter()
                    ->badge()
                    ->label('الحالة')
                    ->searchable(),
                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(InstallationStatusEnum::labels())
                    ->searchable()
                    ->preload(),
                SelectFilter::make('spare_part_id')
                    ->label('قطعة الغيار')
                    ->relationship('sparePart', 'id')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('examine_city_id')
                    ->label('مدينة الفحص')
                    ->relationship('examineCity', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('beneficiary_city_id')
                    ->label('المدينة المستفيدة')
                    ->relationship('beneficiaryCity', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
