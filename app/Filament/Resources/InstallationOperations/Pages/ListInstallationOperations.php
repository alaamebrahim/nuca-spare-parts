<?php

namespace App\Filament\Resources\InstallationOperations\Pages;

use App\Filament\Resources\InstallationOperations\InstallationOperationResource;
use App\Filament\Resources\InstallationOperations\Tables\InstallationOperationsTable;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListInstallationOperations extends ListRecords
{
    protected static string $resource = InstallationOperationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return InstallationOperationsTable::configure($table);
    }
}
