<?php

namespace App\Filament\Pages;

use App\Actions\Backups\CreateDatabaseBackupAction;
use App\Actions\Backups\DeleteDatabaseBackupAction;
use App\DataProcessors\BackupMetricsDataProcessor;
use App\Enums\BackupStatusEnum;
use App\Enums\BackupTypeEnum;
use App\Models\Backup;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Throwable;

class BackupManager extends Page implements HasTable
{
    use HasPageShield;
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'النسخ الاحتياطي';

    protected static ?string $title = 'إدارة النسخ الاحتياطي';

    protected static ?string $slug = 'backup-management';

    protected static ?int $navigationSort = 90;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-circle-stack';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'النظام';
    }

    public function getView(): string
    {
        return 'filament.pages.backup-manager';
    }

    /**
     * @return array{
     *     total: int,
     *     completed: int,
     *     failed: int,
     *     total_size: int,
     *     total_size_human: string,
     *     last_backup_at: ?string
     * }
     */
    public function getMetrics(): array
    {
        return BackupMetricsDataProcessor::overview();
    }

    /**
     * @return list<array{
     *     type: BackupTypeEnum,
     *     label: string,
     *     enabled: bool,
     *     schedule_label: string,
     *     next_run_at: ?string,
     *     last_run_at: ?string,
     *     last_status: ?string
     * }>
     */
    public function getSchedules(): array
    {
        return BackupMetricsDataProcessor::schedules();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Backup $record): string => $record->filename),
                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
                TextColumn::make('size')
                    ->label('الحجم')
                    ->alignCenter()
                    ->sortable()
                    ->formatStateUsing(fn (Backup $record): string => $record->humanSize()),
                TextColumn::make('user.name')
                    ->label('بواسطة')
                    ->placeholder('تلقائي')
                    ->toggleable(),
                TextColumn::make('completed_at')
                    ->label('اكتمل في')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options(collect(BackupTypeEnum::cases())->mapWithKeys(
                        fn (BackupTypeEnum $type): array => [$type->value => $type->getLabel()]
                    )->all()),
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(BackupStatusEnum::cases())->mapWithKeys(
                        fn (BackupStatusEnum $status): array => [$status->value => $status->getLabel()]
                    )->all()),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('download')
                        ->label('تحميل')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->visible(fn (Backup $record): bool => $record->isDownloadable())
                        ->url(fn (Backup $record): ?string => $record->downloadUrl())
                        ->openUrlInNewTab(),
                    Action::make('delete')
                        ->label('حذف')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('حذف النسخة الاحتياطية')
                        ->modalDescription('سيتم حذف الملف من Cloudflare نهائياً. لا يمكن التراجع عن هذا الإجراء.')
                        ->modalSubmitActionLabel('حذف')
                        ->action(function (Backup $record): void {
                            app(DeleteDatabaseBackupAction::class)->run($record);

                            Notification::make()
                                ->title('تم حذف النسخة الاحتياطية')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label('حذف المحدد')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $deleted = app(DeleteDatabaseBackupAction::class)->runMany($records);

                            Notification::make()
                                ->title("تم حذف {$deleted} نسخة احتياطية")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateHeading('لا توجد نسخ احتياطية بعد')
            ->emptyStateDescription('أنشئ نسخة يدوية أو انتظر النسخ التلقائية اليومية/الأسبوعية/الشهرية.')
            ->emptyStateIcon('heroicon-o-circle-stack')
            ->emptyStateActions([
                Action::make('createFirstBackup')
                    ->label('إنشاء نسخة احتياطية')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('إنشاء نسخة احتياطية جديدة')
                    ->modalDescription('سيتم إنشاء نسخة كاملة من قاعدة البيانات ورفعها إلى Cloudflare.')
                    ->modalSubmitActionLabel('بدء النسخ')
                    ->action(fn () => $this->createBackup()),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        return Backup::query()->with('user');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('تحديث')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->resetTable()),
            Action::make('createBackup')
                ->label('إنشاء نسخة يدوية')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('إنشاء نسخة احتياطية يدوية')
                ->modalDescription('سيتم إنشاء نسخة كاملة من قاعدة البيانات ورفعها إلى مجلد backups على Cloudflare. قد يستغرق ذلك بعض الوقت.')
                ->modalSubmitActionLabel('بدء النسخ')
                ->action(fn () => $this->createBackup()),
        ];
    }

    protected function createBackup(): void
    {
        try {
            $backup = app(CreateDatabaseBackupAction::class)->run(
                Auth::id(),
                BackupTypeEnum::Manual,
            );

            Notification::make()
                ->title('تم إنشاء النسخة الاحتياطية بنجاح')
                ->body($backup->name.' ('.$backup->humanSize().')')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('فشل إنشاء النسخة الاحتياطية')
                ->body('تعذر إنشاء النسخة الاحتياطية. راجع السجلات لمزيد من التفاصيل.')
                ->danger()
                ->send();
        }

        $this->resetTable();
    }
}
