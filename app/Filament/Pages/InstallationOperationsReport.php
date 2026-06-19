<?php

namespace App\Filament\Pages;

use App\Data\InstallationOperations\InstallationOperationsFilterData;
use App\Enums\InstallationStatusEnum;
use App\Exports\InstallationOperationsReportExport;
use App\Models\City;
use App\Models\InstallationOperation;
use App\Models\SparePartCategory;
use App\Models\SparePartType;
use App\Traits\HasPageExport;
use App\Traits\HasReportPrintExport;
use App\Traits\InstallationOperationsBaseQueries;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
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

class InstallationOperationsReport extends Page implements HasForms, HasTable
{
    use HasPageExport, HasPageShield, HasReportPrintExport, InteractsWithForms, InteractsWithTable;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-wrench-screwdriver';
    }

    protected static ?string $navigationLabel = 'تقرير عمليات التركيب';

    protected static ?string $title = 'تقرير عمليات التركيب';

    public static function getNavigationGroup(): ?string
    {
        return 'التقارير';
    }

    public function getView(): string
    {
        return 'filament.pages.installation-operations-report';
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
                // Move examine city to the beginning and make it live to affect spare_part_id options
                Select::make('examine_city_id')
                    ->label('مدينة الفحص')
                    ->options(City::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->live()
                    // Clear spare part selection when city filter changes
                    ->afterStateUpdated(fn ($set) => $set('spare_part_id', [])),
                Select::make('spare_part_id')
                    ->label('المهمة')
                    ->columnSpan(2)
                    ->options(function ($get) {
                        $cityIds = $get('examine_city_id') ?? [];

                        return \App\DataProcessors\InstallationOperationsDataProcessor::sparePartOptionsForCities($cityIds);
                    })
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Select::make('beneficiary_city_id')
                    ->label('مدينة المستفيد')
                    ->options(City::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Select::make('status')
                    ->label('حالة التركيب')
                    ->options(InstallationStatusEnum::labels())
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
                DatePicker::make('installation_date_from')
                    ->label('تاريخ التركيب من')
                    ->native(false),
                DatePicker::make('installation_date_to')
                    ->label('تاريخ التركيب إلى')
                    ->native(false),
                DatePicker::make('created_from')
                    ->label('تاريخ الإضافة من')
                    ->native(false),
                DatePicker::make('created_to')
                    ->label('تاريخ الإضافة إلى')
                    ->native(false),
                Select::make('spare_part_type_id')
                    ->label('نوع المهمة')
                    ->options(SparePartType::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Select::make('spare_part_category_id')
                    ->label('فئة المهمة')
                    ->options(SparePartCategory::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->statePath('data')
            ->columns(5);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredQuery())
            ->columns([
                TextColumn::make('sparePart.type.name')
                    ->label('نوع المهمة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sparePart.category.name')
                    ->label('فئة المهمة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sparePart.technical_description')
                    ->label('الوصف الفني')
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 40 ? $state : null;
                    }),
                TextColumn::make('examineCity.name')
                    ->label('مدينة الفحص')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('beneficiaryCity.name')
                    ->label('مدينة المستفيد')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('installation_date')
                    ->label('تاريخ التركيب')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('حالة التركيب')
                    ->formatStateUsing(fn ($state) => InstallationStatusEnum::from($state)->label())
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('description')
                    ->label('كيفية الاستفادة')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('notes')
                    ->label('الملاحظات')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    }),
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
            return InstallationOperation::query()->whereRaw('1 = 0');
        }
        $filters = \App\Data\InstallationOperations\InstallationOperationsFilterData::from($this->data ?? []);

        return \App\Traits\InstallationOperationsBaseQueries::filtered($filters);
    }

    protected function getExportClass(): string
    {
        return InstallationOperationsReportExport::class;
    }

    protected function getExportBaseFilename(): string
    {
        return 'installation-operations-report';
    }

    protected function getExportQuery(): Builder
    {
        $filters = InstallationOperationsFilterData::from($this->data ?? []);

        return InstallationOperationsBaseQueries::filtered($filters)->orderByDesc('created_at');
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getPrintReportAction('reports.installation-operations'),
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
