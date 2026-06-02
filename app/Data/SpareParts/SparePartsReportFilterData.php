<?php

namespace App\Data\SpareParts;

use App\Enums\SparePartStatusEnum;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Data;

class SparePartsReportFilterData extends Data
{
    public function __construct(
        #[ArrayType]
        public ?array $city_id,
        #[ArrayType]
        public ?array $type_id,
        #[ArrayType]
        public ?array $category_id,
        #[ArrayType]
        public ?array $status,
        #[IntegerType]
        public ?int $quantity_from,
        #[IntegerType]
        public ?int $quantity_to,
        #[IntegerType]
        public ?int $cost_from,
        #[IntegerType]
        public ?int $cost_to,
        #[Date]
        public ?string $created_from,
        #[Date]
        public ?string $created_to,
        #[ArrayType]
        public ?array $maintenance_city_id,
        #[IntegerType]
        public ?int $maintenance_cost_from,
        #[IntegerType]
        public ?int $maintenance_cost_to,
    ) {
    }
}

