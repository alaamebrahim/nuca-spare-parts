<?php

namespace App\Filament\Pages;

use App\Data\SpareParts\SparePartsReportFilterData;
use App\Enums\SparePartStatusEnum;
use App\Exports\SparePartsReportExport;
use App\Models\City;
use App\Models\SparePart;
use App\Models\SparePartCategory;
use App\Models\SparePartType;
use App\Traits\HasPageExport;
use App\Traits\HasReportPrintExport;
use App\Traits\SparePartsBaseQueries;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SparePartsReport extends Page implements HasForms, HasTable
{
    use HasPageExport, HasReportPrintExport, InteractsWithForms, InteractsWithTable;

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
                    ->formatStateUsing(fn ($state) => SparePartStatusEnum::from($state)->label())
                    ->badge()
                    ->sortable(),
                TextColumn::make('estimated_cost')
                    ->label('التكلفة التقديرية')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('total_cost')
                    ->label('إجمالي التكلفة')
                    ->getStateUsing(fn (SparePart $record) => \App\DataProcessors\SparePartsDataProcessor::estimatedTotal($record))
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
                    ->getStateUsing(fn (SparePart $record) => \App\DataProcessors\SparePartsDataProcessor::maintenanceTotal($record))
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
        if (! $this->showResults) {
            return SparePart::query()->whereRaw('1 = 0');
        }
        $filters = \App\Data\SpareParts\SparePartsReportFilterData::from($this->data ?? []);

        return \App\Traits\SparePartsBaseQueries::filtered($filters);
    }

    protected function getExportClass(): string
    {
        return SparePartsReportExport::class;
    }

    protected function getExportBaseFilename(): string
    {
        return 'spare-parts-report';
    }

    protected function getExportQuery(): Builder
    {
        $filters = SparePartsReportFilterData::from($this->data ?? []);

        return SparePartsBaseQueries::filtered($filters)->orderByDesc('created_at');
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getPrintReportAction('reports.spare-parts'),
            $this->getExportAction(),
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
