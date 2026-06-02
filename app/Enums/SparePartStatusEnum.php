<?php

namespace App\Enums;

enum SparePartStatusEnum: string
{
    case Maintained = 'Maintained';
    case UsedNeedsMaintainance = 'UsedNeedsMaintainance';
    case UsedInGoodState = 'UsedInGoodState';
    case New = 'New';

    public static function labels(): array
    {
        return [
            self::Maintained->value => 'تم عمل الصيانة لها',
            self::UsedNeedsMaintainance->value => 'مستعمل بحاجة للصيانة',
            self::UsedInGoodState->value => 'مستعمل بحالة جيدة',
            self::New->value => 'جديد',
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::Maintained => 'تم عمل الصيانة لها',
            self::UsedNeedsMaintainance => 'مستعمل بحاجة للصيانة',
            self::UsedInGoodState => 'مستعمل بحالة جيدة',
            self::New => 'جديد',
        };
    }
}
