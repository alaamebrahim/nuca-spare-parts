<?php

namespace App\DataProcessors;

use App\Enums\SparePartStatusEnum;
use App\Models\SparePartImportRow;

class SparePartImportRowsDataProcessor
{
    public static function recalculate(SparePartImportRow $row): void
    {
        $errors = $row->errors ?? [];

        $errors = array_filter($errors, fn (mixed $value): bool => filled($value));

        if (! filled($row->city_id)) {
            $errors['city_name'] ??= 'المدينة غير محددة';
        } else {
            unset($errors['city_name']);
        }

        if (! filled($row->type_id)) {
            $errors['type_name'] ??= 'النوع غير محدد';
        } else {
            unset($errors['type_name']);
        }

        if (! filled($row->category_id)) {
            $errors['category_name'] ??= 'الفئة غير محددة';
        } else {
            unset($errors['category_name']);
        }

        if ($row->quantity === null || $row->quantity < 0) {
            $errors['quantity'] ??= 'الكمية غير صحيحة';
        } else {
            unset($errors['quantity']);
        }

        if (! filled($row->status)) {
            $errors['status'] ??= 'الحالة غير صحيحة';
        } else {
            unset($errors['status']);
        }

        if ($row->status === SparePartStatusEnum::Maintained->value) {
            if (! filled($row->maintenance_city_id)) {
                $errors['maintenance_city_name'] ??= 'مدينة الصيانة غير محددة';
            } else {
                unset($errors['maintenance_city_name']);
            }
        } else {
            unset($errors['maintenance_city_name']);
        }

        $row->forceFill([
            'errors' => empty($errors) ? null : $errors,
            'has_errors' => ! empty($errors),
        ])->save();
    }
}

