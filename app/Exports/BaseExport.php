<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class BaseExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(protected Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    abstract public function headings(): array;

    abstract public function map($record): array;

    public function styles(Worksheet $sheet): array
    {
        $columnCount = count($this->headings());
        $lastColumn = chr(ord('A') + min($columnCount - 1, 25));

        $styles = [
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'right'],
            ],
        ];

        foreach (range('A', $lastColumn) as $column) {
            $styles[$column] = ['alignment' => ['horizontal' => 'right']];
        }

        return $styles;
    }

    protected function formatNumber(float|int|null $value, int $decimals = 2): string
    {
        if ($value === null) {
            return '-';
        }

        return number_format((float) $value, $decimals);
    }
}
