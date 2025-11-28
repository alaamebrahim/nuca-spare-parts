<?php

namespace App\Data\InstallationOperations;

use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Data;

class CreateInstallationOperationInputData extends Data
{
    public function __construct(
        #[IntegerType]
        public int $spare_part_id,
        #[IntegerType]
        public int $beneficiary_city_id,
        #[IntegerType]
        public int $quantity,
        #[Date]
        public string $installation_date,
        public ?string $description,
        public ?string $notes,
    ) {
    }
}

