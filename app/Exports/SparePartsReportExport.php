<?php

namespace App\Exports;

use App\DataProcessors\SparePartsDataProcessor;
use App\Enums\SparePartStatusEnum;
use App\Models\SparePart;

class SparePartsReportExport extends BaseExport
{
    public function headings(): array
    {
        return [
            'م',
            'المدينة',
            'مكان الفحص',
            'نوع المهمة',
            'الفئة',
            'الوصف الفني',
            'الكمية',
            'الحالة',
            'التكلفة التقديرية',
            'إجمالي التكلفة',
            'تكلفة الصيانة',
            'المدينة المنوطة بالصيانة',
            'إجمالي تكلفة الصيانة',
            'تاريخ الإضافة',
        ];
    }

    public function map($record): array
    {
        /** @var SparePart $record */
        static $row = 0;
        $row++;

        return [
            $row,
            $record->city?->name ?? '-',
            $record->location ?? '-',
            $record->type?->name ?? '-',
            $record->category?->name ?? '-',
            $record->technical_description ?? '-',
            $record->quantity,
            SparePartStatusEnum::from($record->status)->label(),
            $this->formatNumber($record->estimated_cost),
            $this->formatNumber(SparePartsDataProcessor::estimatedTotal($record)),
            $this->formatNumber($record->maintenance_cost),
            $record->maintenanceCity?->name ?? '-',
            $this->formatNumber(SparePartsDataProcessor::maintenanceTotal($record)),
            $record->created_at?->format('Y-m-d H:i') ?? '-',
        ];
    }
}
