<?php

namespace App\Filament\Resources\Documents\Tables;

use App\Models\Document;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('description')
                    ->alignCenter()
                    ->label('الوصف')
                    ->limit(200)
                    ->wrap()
                    ->width('40%')
                    ->url(fn(Document $record): string => Storage::disk('public')->url($record->file))
                    ->openUrlInNewTab()
                    ->searchable(),
                TextColumn::make('city.name')
                    ->alignCenter()
                    ->label('المدينة')
                    ->url(fn(Document $record): string => Storage::disk('public')->url($record->file))
                    ->openUrlInNewTab()
                    ->sortable(),
                TextColumn::make('documentType.name')
                    ->alignCenter()
                    ->label('نوع الملف')
                    ->url(fn(Document $record): string => Storage::disk('public')->url($record->file))
                    ->openUrlInNewTab()
                    ->badge()
                    ->sortable(),
                TextColumn::make('document_date')
                    ->date()
                    ->label('تاريخ الملف')
                    ->url(fn(Document $record): string => Storage::disk('public')->url($record->file))
                    ->openUrlInNewTab()
                    ->sortable(),

            ])
            ->filters([
                SelectFilter::make('city_id')
                    ->label('المدينة')
                    ->searchable()
                    ->preload()
                    ->relationship('city', 'name'),
                SelectFilter::make('document_type_id')
                    ->label('نوع الملف')
                    ->searchable()
                    ->preload()
                    ->relationship('documentType', 'name'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),

                    Action::make('view')
                        ->label('عرض')
                        ->url(fn(Document $record): string => Storage::disk('public')->url($record->file))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->openUrlInNewTab(),
                    DeleteAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
