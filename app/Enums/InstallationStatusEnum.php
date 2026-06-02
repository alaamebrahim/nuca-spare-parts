<?php

namespace App\Enums;

enum InstallationStatusEnum: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public static function labels(): array
    {
        return [
            self::Pending->value => 'في الانتظار',
            self::InProgress->value => 'قيد التنفيذ',
            self::Completed->value => 'مكتمل',
            self::Cancelled->value => 'ملغي',
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'في الانتظار',
            self::InProgress => 'قيد التنفيذ',
            self::Completed => 'مكتمل',
            self::Cancelled => 'ملغي',
        };
    }
}
