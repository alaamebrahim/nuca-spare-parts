<?php

namespace App\Filament\Resources\SpareParts\Pages;

use App\Actions\SpareParts\StageSparePartImportBatchAction;
use App\Actions\SpareParts\SaveSparePartImportBatchAction;
use App\DataProcessors\SparePartImportRowsDataProcessor;
use App\Filament\Resources\SpareParts\SparePartResource;
use App\Exports\SparePartsImportTemplateExport;
use App\Models\City;
use App\Models\SparePartCategory;
use App\Models\SparePartImportBatch;
use App\Models\SparePartImportRow;
use App\Models\SparePartType;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MassImportSpareParts extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = SparePartResource::class;

    protected static ?string $title = 'استيراد المهمات (Excel)';

    public ?array $data = [];

    public ?int $batchId = null;

    public function mount(): void
    {
        $userId = $this->getUserId();

        $this->batchId = SparePartImportBatch::query()
            ->where('status', 'draft')
            ->where('user_id', $userId)
            ->latest('id')
            ->value('id');

        $this->form->fill();
    }

    public function getView(): string
    {
        return 'filament.resources.spare-parts.pages.mass-import';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('رفع ملف Excel')
                    ->description('قم بتحميل نموذج Excel من الأعلى، ثم ارفع الملف هنا لمراجعة البيانات قبل الحفظ.')
                    ->components([
                        FileUpload::make('file')
                            ->label('ملف Excel')
                            ->acceptedFileTypes([
                                // Prefer MIME types (more reliable in Filament v4):
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                // Fallback to extensions (helps some OS file pickers):
                                '.xlsx',
                                '.xls',
                            ])
                            ->directory('imports/spare-parts/' . $this->getUserId())
                            ->disk('local')
                            ->visibility('private')
                            ->maxSize(1024 * 20)
                            ->required(),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('تحميل نموذج Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(fn () => Excel::download(new SparePartsImportTemplateExport(), 'spare-parts-import-template.xlsx')),
            Action::make('saveToDatabase')
                ->label('حفظ في قاعدة البيانات')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (): bool => filled($this->batchId))
                ->disabled(fn (): bool => $this->batchHasErrors())
                ->requiresConfirmation()
                ->action(function (): void {
                    if (! $this->batchId) {
                        return;
                    }

                    try {
                        $count = SaveSparePartImportBatchAction::run($this->batchId);

                        Notification::make()
                            ->title('تم الحفظ بنجاح')
                            ->body("تم إضافة {$count} مهمة.")
                            ->success()
                            ->send();

                        $this->batchId = null;
                        $this->data = [];
                        $this->form->fill();
                        $this->resetTable();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('تعذر حفظ البيانات')
                            ->body('راجع الأخطاء في الجدول ثم حاول مرة أخرى.')
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('cancelBatch')
                ->label('إلغاء الاستيراد')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->visible(fn (): bool => filled($this->batchId))
                ->requiresConfirmation()
                ->action(function (): void {
                    if (! $this->batchId) {
                        return;
                    }

                    $batch = SparePartImportBatch::findOrFail($this->batchId);
                    $batch->update(['status' => 'cancelled']);

                    Notification::make()
                        ->title('تم إلغاء عملية الاستيراد')
                        ->success()
                        ->send();

                    $this->batchId = null;
                    $this->resetTable();
                }),
        ];
    }

    public function stageImport(): void
    {
        $this->validate();

        $state = $this->form->getState();
        $relativePath = $state['file'] ?? null;

        if (is_array($relativePath)) {
            $relativePath = $relativePath[0] ?? null;
        }

        if (! is_string($relativePath) || $relativePath === '') {
            Notification::make()
                ->title('الرجاء رفع ملف Excel')
                ->danger()
                ->send();

            return;
        }

        $fullPath = Storage::disk('local')->path($relativePath);

        $batch = StageSparePartImportBatchAction::run(
            filePath: $fullPath,
            originalFilename: basename($relativePath),
            userId: $this->getUserId(),
            existingBatchId: $this->batchId,
        );

        $this->batchId = $batch->id;

        Notification::make()
            ->title('تم استيراد الملف بنجاح')
            ->body('راجع البيانات قبل الحفظ.')
            ->success()
            ->send();

        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        $cityOptions = City::query()->orderBy('name')->pluck('name', 'id')->all();
        $typeOptions = SparePartType::query()->orderBy('name')->pluck('name', 'id')->all();
        $categoryOptions = SparePartCategory::query()->orderBy('name')->pluck('name', 'id')->all();

        return $table
            ->query($this->getRowsQuery())
            ->columns([
                TextColumn::make('city_name_raw')
                    ->label('المدينة (بالملف)')
                    ->extraAttributes(fn (SparePartImportRow $record): array => $record->errors['city_name'] ?? null ? ['class' => 'import-cell-error'] : []),
                SelectColumn::make('city_id')
                    ->label('المدينة (من النظام)')
                    ->options($cityOptions)
                    ->searchableOptions()
                    ->preloadOptions()
                    ->placeholder('اختر مدينة')
                    ->afterStateUpdated(fn (mixed $state, SparePartImportRow $record) => SparePartImportRowsDataProcessor::recalculate($record)),
                TextColumn::make('type_name_raw')
                    ->label('النوع (بالملف)')
                    ->extraAttributes(fn (SparePartImportRow $record): array => $record->errors['type_name'] ?? null ? ['class' => 'import-cell-error'] : []),
                SelectColumn::make('type_id')
                    ->label('النوع (من النظام)')
                    ->options($typeOptions)
                    ->searchableOptions()
                    ->preloadOptions()
                    ->placeholder('اختر نوع')
                    ->afterStateUpdated(fn (mixed $state, SparePartImportRow $record) => SparePartImportRowsDataProcessor::recalculate($record)),
                TextColumn::make('category_name_raw')
                    ->label('الفئة (بالملف)')
                    ->extraAttributes(fn (SparePartImportRow $record): array => $record->errors['category_name'] ?? null ? ['class' => 'import-cell-error'] : []),
                SelectColumn::make('category_id')
                    ->label('الفئة (من النظام)')
                    ->options($categoryOptions)
                    ->searchableOptions()
                    ->preloadOptions()
                    ->placeholder('اختر فئة')
                    ->afterStateUpdated(fn (mixed $state, SparePartImportRow $record) => SparePartImportRowsDataProcessor::recalculate($record)),
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->extraAttributes(fn (SparePartImportRow $record): array => $record->errors['quantity'] ?? null ? ['class' => 'import-cell-error'] : []),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->extraAttributes(fn (SparePartImportRow $record): array => $record->errors['status'] ?? null ? ['class' => 'import-cell-error'] : []),
                TextColumn::make('maintenance_city_name_raw')
                    ->label('مدينة الصيانة (بالملف)')
                    ->extraAttributes(fn (SparePartImportRow $record): array => $record->errors['maintenance_city_name'] ?? null ? ['class' => 'import-cell-error'] : []),
                SelectColumn::make('maintenance_city_id')
                    ->label('مدينة الصيانة (من النظام)')
                    ->options($cityOptions)
                    ->searchableOptions()
                    ->preloadOptions()
                    ->placeholder('اختر مدينة')
                    ->afterStateUpdated(fn (mixed $state, SparePartImportRow $record) => SparePartImportRowsDataProcessor::recalculate($record)),
            ])
            ->recordActions([
                Action::make('deleteRow')
                    ->label('حذف')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (SparePartImportRow $record): void {
                        $record->delete();
                        $this->resetTable();
                    }),
                Action::make('editRow')
                    ->label('تعديل')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('location_raw')->label('مكان الفحص'),
                        \Filament\Forms\Components\Textarea::make('technical_description_raw')->label('الوصف الفني'),
                        \Filament\Forms\Components\TextInput::make('quantity')->label('الكمية')->numeric()->required()->minValue(0),
                        \Filament\Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options(\App\Enums\SparePartStatusEnum::labels())
                            ->searchable()
                            ->preload()
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('estimated_cost')->label('التكلفة التقديرية للوحدة')->numeric(),
                        \Filament\Forms\Components\TextInput::make('maintenance_cost')->label('تكلفة الصيانة')->numeric(),
                    ])
                    ->fillForm(fn (SparePartImportRow $record): array => [
                        'location_raw' => $record->location_raw,
                        'technical_description_raw' => $record->technical_description_raw,
                        'quantity' => $record->quantity,
                        'status' => $record->status,
                        'estimated_cost' => $record->estimated_cost,
                        'maintenance_cost' => $record->maintenance_cost,
                    ])
                    ->action(function (array $data, SparePartImportRow $record): void {
                        $record->forceFill($data)->save();
                        SparePartImportRowsDataProcessor::recalculate($record);
                        $this->resetTable();
                    }),
            ])
            ->defaultPaginationPageOption(25)
            ->emptyStateHeading('لا توجد بيانات مستوردة بعد')
            ->emptyStateDescription('قم برفع ملف Excel ثم اضغط زر “استيراد الملف”.');
    }

    protected function getRowsQuery(): Builder
    {
        if (! $this->batchId) {
            return SparePartImportRow::query()->whereRaw('1 = 0');
        }

        return SparePartImportRow::query()
            ->where('batch_id', $this->batchId)
            ->latest('id');
    }

    private function batchHasErrors(): bool
    {
        if (! $this->batchId) {
            return true;
        }

        return SparePartImportRow::query()
            ->where('batch_id', $this->batchId)
            ->where('has_errors', true)
            ->exists();
    }

    private function getUserId(): int
    {
        return (int) (Auth::id() ?? 0);
    }
}

