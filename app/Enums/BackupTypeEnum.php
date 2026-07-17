<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BackupTypeEnum: string implements HasColor, HasLabel
{
    case Manual = 'manual';
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    public function getLabel(): string
    {
        return match ($this) {
            self::Manual => 'يدوي',
            self::Daily => 'يومي',
            self::Weekly => 'أسبوعي',
            self::Monthly => 'شهري',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Manual => 'gray',
            self::Daily => 'info',
            self::Weekly => 'warning',
            self::Monthly => 'success',
        };
    }

    public function readableNamePrefix(): string
    {
        return match ($this) {
            self::Manual => 'نسخة احتياطية',
            self::Daily => 'نسخة يومية',
            self::Weekly => 'نسخة أسبوعية',
            self::Monthly => 'نسخة شهرية',
        };
    }

    public function isScheduled(): bool
    {
        return $this !== self::Manual;
    }

    /**
     * @return list<self>
     */
    public static function scheduled(): array
    {
        return [
            self::Daily,
            self::Weekly,
            self::Monthly,
        ];
    }
}
