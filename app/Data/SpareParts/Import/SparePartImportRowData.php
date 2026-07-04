<?php

namespace App\Data\SpareParts\Import;

use App\Enums\SparePartStatusEnum;
use Spatie\LaravelData\Data;

class SparePartImportRowData extends Data
{
    public function __construct(
        public ?string $city_name,
        public ?string $type_name,
        public ?string $category_name,
        public ?string $maintenance_city_name,
        public ?string $location,
        public ?string $technical_description,
        public ?int $quantity,
        public ?string $status,
        public ?float $estimated_cost,
        public ?float $maintenance_cost,

        /** @var array<string, string> */
        public array $errors = [],
    ) {}

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromExcelRow(array $row): self
    {
        $cityName = self::stringOrNull($row['city_name'] ?? null);
        $typeName = self::stringOrNull($row['type_name'] ?? null);
        $categoryName = self::stringOrNull($row['category_name'] ?? null);
        $maintenanceCityName = self::stringOrNull($row['maintenance_city_name'] ?? null);
        $location = self::stringOrNull($row['location'] ?? null);
        $technicalDescription = self::stringOrNull($row['technical_description'] ?? null);

        $quantity = self::intOrNull($row['quantity'] ?? null) ?? 0;
        $estimatedCost = self::floatOrNull($row['estimated_cost'] ?? null) ?? 0;
        $maintenanceCost = self::floatOrNull($row['maintenance_cost'] ?? null) ?? 0;

        $status = self::normalizeStatus(self::stringOrNull($row['status'] ?? null));

        $errors = [];
        if (! filled($cityName)) {
            $errors['city_name'] = 'المدينة مطلوبة';
        }
        if (! filled($typeName)) {
            $errors['type_name'] = 'نوع المهمة مطلوب';
        }
        if (! filled($categoryName)) {
            $errors['category_name'] = 'الفئة مطلوبة';
        }
        if ($quantity < 0) {
            $errors['quantity'] = 'الكمية غير صحيحة';
        }
        if (! filled($status)) {
            $errors['status'] = 'الحالة غير صحيحة';
        }

        if (($status === SparePartStatusEnum::Maintained->value) && (! filled($maintenanceCityName))) {
            $errors['maintenance_city_name'] = 'مدينة الصيانة مطلوبة عند اختيار حالة الصيانة';
        }

        return new self(
            city_name: $cityName,
            type_name: $typeName,
            category_name: $categoryName,
            maintenance_city_name: $maintenanceCityName,
            location: $location,
            technical_description: $technicalDescription,
            quantity: $quantity,
            status: $status,
            estimated_cost: $estimatedCost,
            maintenance_cost: $maintenanceCost,
            errors: $errors,
        );
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $value = str_replace([',', ' '], '', (string) $value);

        return is_numeric($value) ? (int) $value : null;
    }

    private static function floatOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = str_replace([' ', ','], ['', '.'], (string) $value);

        return is_numeric($value) ? (float) $value : null;
    }

    private static function normalizeStatus(?string $status): ?string
    {
        if (! filled($status)) {
            return null;
        }

        foreach (SparePartStatusEnum::cases() as $case) {
            if (strcasecmp($case->value, $status) === 0) {
                return $case->value;
            }
        }

        $labelToValue = array_flip(SparePartStatusEnum::labels());

        return $labelToValue[$status] ?? null;
    }
}

