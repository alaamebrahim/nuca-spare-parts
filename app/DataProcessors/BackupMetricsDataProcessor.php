<?php

namespace App\DataProcessors;

use App\Enums\BackupStatusEnum;
use App\Enums\BackupTypeEnum;
use App\Models\Backup;
use Carbon\CarbonImmutable;
use Illuminate\Support\Number;

class BackupMetricsDataProcessor
{
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
    public static function overview(): array
    {
        $total = Backup::query()->count();
        $completed = Backup::query()->where('status', BackupStatusEnum::Completed)->count();
        $failed = Backup::query()->where('status', BackupStatusEnum::Failed)->count();
        $totalSize = (int) Backup::query()
            ->where('status', BackupStatusEnum::Completed)
            ->sum('size');
        $lastBackupAt = Backup::query()
            ->where('status', BackupStatusEnum::Completed)
            ->latest('completed_at')
            ->value('completed_at');

        return [
            'total' => $total,
            'completed' => $completed,
            'failed' => $failed,
            'total_size' => $totalSize,
            'total_size_human' => $totalSize > 0 ? Number::fileSize($totalSize) : '0 B',
            'last_backup_at' => $lastBackupAt?->format('Y-m-d H:i'),
        ];
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
    public static function schedules(): array
    {
        return collect(BackupTypeEnum::scheduled())
            ->map(function (BackupTypeEnum $type): array {
                $config = config('backup.schedules.'.$type->value, []);
                $enabled = (bool) ($config['enabled'] ?? false);
                $lastBackup = Backup::query()
                    ->where('type', $type)
                    ->latest('created_at')
                    ->first();

                return [
                    'type' => $type,
                    'label' => $type->getLabel(),
                    'enabled' => $enabled,
                    'schedule_label' => self::scheduleLabel($type, $config),
                    'next_run_at' => $enabled ? self::nextRunAt($type, $config)?->format('Y-m-d H:i') : null,
                    'last_run_at' => $lastBackup?->created_at?->format('Y-m-d H:i'),
                    'last_status' => $lastBackup?->status?->getLabel(),
                ];
            })
            ->all();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected static function scheduleLabel(BackupTypeEnum $type, array $config): string
    {
        $time = (string) ($config['time'] ?? '00:00');

        return match ($type) {
            BackupTypeEnum::Daily => "يومياً الساعة {$time}",
            BackupTypeEnum::Weekly => 'أسبوعياً يوم '.self::weekdayLabel((int) ($config['day'] ?? 0))." الساعة {$time}",
            BackupTypeEnum::Monthly => 'شهرياً يوم '.((int) ($config['day'] ?? 1))." الساعة {$time}",
            default => '—',
        };
    }

    /**
     * @param  array<string, mixed>  $config
     */
    protected static function nextRunAt(BackupTypeEnum $type, array $config): ?CarbonImmutable
    {
        $now = CarbonImmutable::now();
        $time = (string) ($config['time'] ?? '00:00');
        [$hour, $minute] = array_map('intval', array_pad(explode(':', $time), 2, 0));

        return match ($type) {
            BackupTypeEnum::Daily => self::nextDaily($now, $hour, $minute),
            BackupTypeEnum::Weekly => self::nextWeekly($now, (int) ($config['day'] ?? 0), $hour, $minute),
            BackupTypeEnum::Monthly => self::nextMonthly($now, (int) ($config['day'] ?? 1), $hour, $minute),
            default => null,
        };
    }

    protected static function nextDaily(CarbonImmutable $now, int $hour, int $minute): CarbonImmutable
    {
        $candidate = $now->setTime($hour, $minute);

        return $candidate->greaterThan($now) ? $candidate : $candidate->addDay();
    }

    protected static function nextWeekly(CarbonImmutable $now, int $day, int $hour, int $minute): CarbonImmutable
    {
        $candidate = $now->setTime($hour, $minute);

        if ($candidate->dayOfWeek === $day && $candidate->greaterThan($now)) {
            return $candidate;
        }

        return $candidate->next($day)->setTime($hour, $minute);
    }

    protected static function nextMonthly(CarbonImmutable $now, int $day, int $hour, int $minute): CarbonImmutable
    {
        $day = max(1, min(28, $day));
        $candidate = $now->setDay($day)->setTime($hour, $minute);

        return $candidate->greaterThan($now) ? $candidate : $candidate->addMonthNoOverflow()->setDay($day)->setTime($hour, $minute);
    }

    protected static function weekdayLabel(int $day): string
    {
        return match ($day) {
            0 => 'الأحد',
            1 => 'الاثنين',
            2 => 'الثلاثاء',
            3 => 'الأربعاء',
            4 => 'الخميس',
            5 => 'الجمعة',
            6 => 'السبت',
            default => 'الأحد',
        };
    }
}
