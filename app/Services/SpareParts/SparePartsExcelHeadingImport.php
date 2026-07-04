<?php

namespace App\Services\SpareParts;

use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SparePartsExcelHeadingImport implements SkipsEmptyRows, ToArray, WithHeadingRow
{
    /**
     * @param  array<int, array<string, mixed>>  $array
     */
    public function array(array $array): void
    {
        // Intentionally empty. We use Excel::toArray() return value.
    }
}
