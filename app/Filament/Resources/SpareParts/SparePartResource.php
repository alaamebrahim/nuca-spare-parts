<?php

namespace App\Filament\Resources\SpareParts;

use App\Filament\Resources\SpareParts\Pages\CreateSparePart;
use App\Filament\Resources\SpareParts\Pages\EditSparePart;
use App\Filament\Resources\SpareParts\Pages\MassImportSpareParts;
use App\Filament\Resources\SpareParts\Pages\ListSpareParts;
use App\Filament\Resources\SpareParts\Schemas\SparePartForm;
use App\Filament\Resources\SpareParts\Tables\SparePartsTable;
use App\Models\SparePart;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SparePartResource extends Resource
{
    protected static ?string $model = SparePart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCubeTransparent;

    protected static ?string $recordTitleAttribute = 'technical_description';

    protected static ?string $navigationLabel = 'المهمات';
    protected static ?string $modelLabel = 'مهمة';
    protected static ?string $pluralModelLabel  = 'المهمات';

    public static function form(Schema $schema): Schema
    {
        return SparePartForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SparePartsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpareParts::route('/'),
            'create' => CreateSparePart::route('/create'),
            'edit' => EditSparePart::route('/{record}/edit'),
            'import' => MassImportSpareParts::route('/import'),
        ];
    }
}
