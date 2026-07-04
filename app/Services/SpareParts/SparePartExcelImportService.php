<?php

namespace App\Services\SpareParts;

use App\Data\SpareParts\Import\SparePartImportRowData;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class SparePartExcelImportService
{
    /**
     * Map Arabic template headers (matching create form labels) to internal keys.
     */
    private const ARABIC_HEADER_MAP = [
        'المدينة التي تم الفحص بها' => 'city_name',
        'نوع المهمة' => 'type_name',
        'الفئة' => 'category_name',
        'مكان الفحص' => 'location',
        'الوصف الفني' => 'technical_description',
        'الكمية' => 'quantity',
        'الحالة' => 'status',
        'التكلفة التقديرية للوحدة' => 'estimated_cost',
        'تكلفة الصيانة' => 'maintenance_cost',
        'تكلفة الصيانة ' => 'maintenance_cost',
        'المدينة المنوطة بالصيانة' => 'maintenance_city_name',
    ];

    /**
     * @return Collection<int, SparePartImportRowData>
     */
    public function parse(string $filePath): Collection
    {
        HeadingRowFormatter::default(HeadingRowFormatter::FORMATTER_NONE);

        try {
            $sheets = Excel::toArray(new SparePartsExcelHeadingImport, $filePath);
        } finally {
            HeadingRowFormatter::reset();
        }

        // Always read the first sheet (Template). The export contains a second sheet (Lookups).
        $sheetRows = $sheets[0] ?? [];

        return collect($sheetRows)
            ->map(fn (array $row) => $this->normalizeHeaders($row))
            ->map(fn (array $row) => SparePartImportRowData::fromExcelRow($row))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeHeaders(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $rawKey = trim((string) $key);

            $mapped = self::ARABIC_HEADER_MAP[$rawKey] ?? null;
            if ($mapped) {
                $normalized[$mapped] = $value;

                continue;
            }

            $normalizedKey = trim(strtolower($rawKey));
            $normalized[$normalizedKey] = $value;
        }

        return $normalized;
    }
}
