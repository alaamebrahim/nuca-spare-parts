<?php

namespace App\Filament\Resources\InstallationOperations\Pages;

use App\Filament\Resources\InstallationOperations\InstallationOperationResource;
use App\Filament\Resources\InstallationOperations\Schemas\InstallationOperationForm;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateInstallationOperation extends CreateRecord
{
    protected static string $resource = InstallationOperationResource::class;

    public function form(Schema $schema): Schema
    {
        return InstallationOperationForm::configure($schema);
    }
}
