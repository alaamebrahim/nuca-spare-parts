<?php

namespace App\Support;

class ReportNumber
{
    public static function format(float|int|null $value, int $decimals = 2): string
    {
        if ($value === null) {
            return '-';
        }

        return number_format((float) $value, $decimals);
    }
}
