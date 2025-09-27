<?php

namespace App\Filament\Resources\InstallationOperations;

use App\Filament\Resources\InstallationOperations\Pages;
use App\Models\InstallationOperation;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class InstallationOperationResource extends Resource
{
    protected static ?string $model = InstallationOperation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Bars3;

    protected static ?string $navigationLabel = 'عمليات النقل والتركيب';

    protected static ?string $modelLabel = 'عملية نقل وتركيب';

    protected static ?string $pluralModelLabel = 'عمليات النقل والتركيب';

    protected static ?int $navigationSort = 1000;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstallationOperations::route('/'),
            'create' => Pages\CreateInstallationOperation::route('/create'),
            'edit' => Pages\EditInstallationOperation::route('/{record}/edit'),
        ];
    }
}
