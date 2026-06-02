<?php

namespace App\Filament\Resources\SparePartCategories;

use App\Filament\Resources\SparePartCategories\Pages\CreateSparePartCategory;
use App\Filament\Resources\SparePartCategories\Pages\EditSparePartCategory;
use App\Filament\Resources\SparePartCategories\Pages\ListSparePartCategories;
use App\Filament\Resources\SparePartCategories\Schemas\SparePartCategoryForm;
use App\Filament\Resources\SparePartCategories\Tables\SparePartCategoriesTable;
use App\Models\SparePartCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SparePartCategoryResource extends Resource
{
    protected static ?string $model = SparePartCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'فئات المهمات';
    protected static ?string $modelLabel = 'فئة ';
    protected static ?string $pluralModelLabel  = 'الفئات';

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة البيانات';
    }

    public static function form(Schema $schema): Schema
    {
        return SparePartCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SparePartCategoriesTable::configure($table);
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
            'index' => ListSparePartCategories::route('/'),
            'create' => CreateSparePartCategory::route('/create'),
            'edit' => EditSparePartCategory::route('/{record}/edit'),
        ];
    }
}
