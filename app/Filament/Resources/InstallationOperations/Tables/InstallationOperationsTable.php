<?php

namespace App\Filament\Resources\InstallationOperations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section as ComponentsSection;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\ActionGroup;

class InstallationOperationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

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
                TextColumn::make('description')
                    ->label('كيفية الاستفادة')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
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
                ActionGroup::make([
                    ViewAction::make()
                        ->schema([
                            ComponentsSection::make('تفاصيل العملية')
                                ->columns(1)
                                ->schema([
                                    TextEntry::make('sparePart.type.name')
                                        ->label('نوع القطعة'),
                                    TextEntry::make('sparePart.category.name')
                                        ->label('فئة القطعة'),
                                    TextEntry::make('examineCity.name')
                                        ->label('مدينة الفحص'),
                                    TextEntry::make('beneficiaryCity.name')
                                        ->label('المدينة المستفيدة'),
                                    TextEntry::make('quantity')
                                        ->label('الكمية'),
                                    TextEntry::make('installation_date')
                                        ->label('تاريخ التركيب')
                                        ->date(),
                                    TextEntry::make('description')
                                        ->label('كيفية الاستفادة')
                                        ->default('-'),
                                    TextEntry::make('notes')
                                        ->label('ملاحظات')
                                        ->default('-'),
                                    TextEntry::make('created_at')
                                        ->label('تاريخ الإنشاء')
                                        ->dateTime(),
                                ]),
                        ]),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
