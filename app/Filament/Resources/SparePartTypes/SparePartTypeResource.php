<?php

namespace App\Filament\Resources\SparePartTypes;

use App\Filament\Resources\SparePartTypes\Pages\CreateSparePartType;
use App\Filament\Resources\SparePartTypes\Pages\EditSparePartType;
use App\Filament\Resources\SparePartTypes\Pages\ListSparePartTypes;
use App\Filament\Resources\SparePartTypes\Schemas\SparePartTypeForm;
use App\Filament\Resources\SparePartTypes\Tables\SparePartTypesTable;
use App\Models\SparePartType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SparePartTypeResource extends Resource
{
    protected static ?string $model = SparePartType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'أنواع المهمات';

    protected static ?string $modelLabel = 'نوع ';

    protected static ?string $pluralModelLabel = 'الأنواع';

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة البيانات';
    }

    public static function form(Schema $schema): Schema
    {
        return SparePartTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SparePartTypesTable::configure($table);
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
            'index' => ListSparePartTypes::route('/'),
            'create' => CreateSparePartType::route('/create'),
            'edit' => EditSparePartType::route('/{record}/edit'),
        ];
    }
}
