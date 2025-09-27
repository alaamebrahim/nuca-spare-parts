<?php

namespace App\Filament\Resources\InstallationOperations\Pages;

use App\Filament\Resources\InstallationOperations\InstallationOperationResource;
use App\Filament\Resources\InstallationOperations\Schemas\InstallationOperationForm;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditInstallationOperation extends EditRecord
{
    protected static string $resource = InstallationOperationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return InstallationOperationForm::configure($schema);
    }
}
