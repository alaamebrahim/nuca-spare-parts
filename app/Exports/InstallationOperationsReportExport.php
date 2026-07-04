<?php

namespace App\Exports;

use App\Models\InstallationOperation;

class InstallationOperationsReportExport extends BaseExport
{
    public function headings(): array
    {
        return [
            'م',
            'نوع المهمة',
            'فئة المهمة',
            'الوصف الفني',
            'مدينة الفحص',
            'مدينة المستفيد',
            'الكمية',
            'تاريخ التركيب',
            'كيفية الاستفادة',
            'الملاحظات',
            'تاريخ الإضافة',
        ];
    }

    public function map($record): array
    {
        /** @var InstallationOperation $record */
        static $row = 0;
        $row++;

        return [
            $row,
            $record->sparePart?->type?->name ?? '-',
            $record->sparePart?->category?->name ?? '-',
            $record->sparePart?->technical_description ?? '-',
            $record->examineCity?->name ?? '-',
            $record->beneficiaryCity?->name ?? '-',
            $record->quantity,
            $record->installation_date?->format('Y-m-d') ?? '-',
            $record->description ?? '-',
            $record->notes ?? '-',
            $record->created_at?->format('Y-m-d H:i') ?? '-',
        ];
    }
}
