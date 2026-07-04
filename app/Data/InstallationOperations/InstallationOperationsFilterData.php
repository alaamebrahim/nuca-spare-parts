<?php

namespace App\Data\InstallationOperations;

use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Data;

class InstallationOperationsFilterData extends Data
{
    public function __construct(
        #[ArrayType]
        public ?array $spare_part_id,
        #[ArrayType]
        public ?array $examine_city_id,
        #[ArrayType]
        public ?array $beneficiary_city_id,
        #[IntegerType]
        public ?int $quantity_from,
        #[IntegerType]
        public ?int $quantity_to,
        #[Date]
        public ?string $installation_date_from,
        #[Date]
        public ?string $installation_date_to,
        #[Date]
        public ?string $created_from,
        #[Date]
        public ?string $created_to,
        #[ArrayType]
        public ?array $spare_part_type_id,
        #[ArrayType]
        public ?array $spare_part_category_id,
    ) {
    }
}

