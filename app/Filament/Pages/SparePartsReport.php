<?php

namespace App\Filament\Pages;

use App\Enums\SparePartStatusEnum;
use App\Models\City;
use App\Models\SparePart;
use App\Models\SparePartCategory;
use App\Models\SparePartType;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SparePartsReport extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-chart-bar';
    }
    protected static ?string $navigationLabel = 'تقرير المهمات';
    protected static ?string $title = 'تقرير المهمات';

    public static function getNavigationGroup(): ?string
    {
        return 'التقارير';
    }

    public function getView(): string
    {
        return 'filament.pages.spare-parts-report';
    }

    public ?array $data = [];
    public bool $showResults = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('city_id')
                    ->label('المدينة')
                    ->columnSpan(2)
                    ->options(City::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Select::make('type_id')
                    ->label('نوع المهمة')
                    ->options(SparePartType::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Select::make('category_id')
                    ->label('الفئة')
                    ->options(SparePartCategory::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Select::make('status')
                    ->label('الحالة')
                    ->options(SparePartStatusEnum::labels())
                    ->searchable()
                    ->preload()
                    ->multiple(),
                TextInput::make('quantity_from')
                    ->label('الكمية من')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('quantity_to')
                    ->label('الكمية إلى')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('cost_from')
                    ->label('التكلفة من')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('cost_to')
                    ->label('التكلفة إلى')
                    ->numeric()
                    ->minValue(0),
                DatePicker::make('created_from')
                    ->label('تاريخ الإضافة من')
                    ->native(false),
                DatePicker::make('created_to')
                    ->label('تاريخ الإضافة إلى')
                    ->native(false),
                Select::make('maintenance_city_id')
                    ->label('المدينة المنوطة بالصيانة')
                    ->options(City::pluck('name', 'id'))
                    ->searchable()
                    ->columnSpan(2)
                    ->preload()
                    ->multiple(),
                TextInput::make('maintenance_cost_from')
                    ->label('تكلفة الصيانة من')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('maintenance_cost_to')
                    ->label('تكلفة الصيانة إلى')
                    ->numeric()
                    ->minValue(0),
            ])
            ->statePath('data')
            ->columns(5);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredQuery())
            ->columns([
                TextColumn::make('city.name')
                    ->label('المدينة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('location')
                    ->label('مكان الفحص')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('type.name')
                    ->label('نوع المهمة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('الفئة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('technical_description')
                    ->label('الوصف الفني')
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 40 ? $state : null;
                    }),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn($state) => SparePartStatusEnum::from($state)->label())
                    ->badge()
                    ->sortable(),
                TextColumn::make('estimated_cost')
                    ->label('التكلفة التقديرية')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('total_cost')
                    ->label('إجمالي التكلفة')
                    ->getStateUsing(fn(SparePart $record) => $record->quantity * $record->estimated_cost)
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('maintenance_cost')
                    ->label('تكلفة الصيانة')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('maintenance_city.name')
                    ->label('المدينة المنوطة بالصيانة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('total_maintenance_cost')
                    ->label('إجمالي تكلفة الصيانة')
                    ->getStateUsing(fn(SparePart $record) => $record->quantity * $record->maintenance_cost)
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('لا توجد بيانات')
            ->emptyStateDescription('اضغط على زر "البحث" لعرض النتائج')
            ->emptyStateIcon('heroicon-o-document-magnifying-glass');
    }

    protected function getFilteredQuery(): Builder
    {
        if (!$this->showResults) {
            return SparePart::query()->whereRaw('1 = 0'); // Return empty result
        }

        $query = SparePart::query()->with(['city', 'type', 'category', 'maintenanceCity']);

        if (!empty($this->data['city_id'])) {
            $query->whereIn('city_id', $this->data['city_id']);
        }

        if (!empty($this->data['type_id'])) {
            $query->whereIn('type_id', $this->data['type_id']);
        }

        if (!empty($this->data['category_id'])) {
            $query->whereIn('category_id', $this->data['category_id']);
        }

        if (!empty($this->data['status'])) {
            $query->whereIn('status', $this->data['status']);
        }

        if (!empty($this->data['quantity_from'])) {
            $query->where('quantity', '>=', $this->data['quantity_from']);
        }

        if (!empty($this->data['quantity_to'])) {
            $query->where('quantity', '<=', $this->data['quantity_to']);
        }

        if (!empty($this->data['cost_from'])) {
            $query->where('estimated_cost', '>=', $this->data['cost_from']);
        }

        if (!empty($this->data['cost_to'])) {
            $query->where('estimated_cost', '<=', $this->data['cost_to']);
        }

        if (!empty($this->data['created_from'])) {
            $query->whereDate('created_at', '>=', $this->data['created_from']);
        }

        if (!empty($this->data['created_to'])) {
            $query->whereDate('created_at', '<=', $this->data['created_to']);
        }

        if (!empty($this->data['maintenance_city_id'])) {
            $query->whereIn('maintenance_city_id', $this->data['maintenance_city_id']);
        }

        if (!empty($this->data['maintenance_cost_from'])) {
            $query->where('maintenance_cost', '>=', $this->data['maintenance_cost_from']);
        }

        if (!empty($this->data['maintenance_cost_to'])) {
            $query->where('maintenance_cost', '<=', $this->data['maintenance_cost_to']);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('search')
                ->label('البحث')
                ->icon('heroicon-o-magnifying-glass')
                ->color('primary')
                ->action(function () {
                    $this->showResults = true;
                    $this->resetTable();
                }),
            Action::make('clear')
                ->label('مسح الفلاتر')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->action(function () {
                    $this->form->fill([]);
                    $this->showResults = false;
                    $this->resetTable();
                }),
        ];
    }
}
