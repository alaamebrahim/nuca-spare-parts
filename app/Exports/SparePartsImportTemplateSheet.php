<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class SparePartsImportTemplateSheet implements FromArray, WithHeadings, WithTitle, WithEvents
{
    private const TEMPLATE_ROWS_WITH_VALIDATION = 300;

    public function title(): string
    {
        return 'Template';
    }

    public function headings(): array
    {
        return [
            'المدينة التي تم الفحص بها',
            'نوع المهمة',
            'الفئة',
            'مكان الفحص',
            'الوصف الفني',
            'الكمية',
            'الحالة',
            'التكلفة التقديرية للوحدة',
            'تكلفة الصيانة ',
            'المدينة المنوطة بالصيانة',
        ];
    }

    public function array(): array
    {
        return [
            [
                'القاهرة',
                'نوع مثال',
                'فئة مثال',
                'مكان الفحص',
                'وصف فني',
                1,
                'New',
                0,
                0,
                null,
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $this->applyListValidation($sheet, column: 'A', lookupColumn: 'A', allowBlank: false); // city_name
                $this->applyListValidation($sheet, column: 'B', lookupColumn: 'B', allowBlank: false); // type_name
                $this->applyListValidation($sheet, column: 'C', lookupColumn: 'C', allowBlank: false); // category_name
                $this->applyListValidation($sheet, column: 'G', lookupColumn: 'D', allowBlank: false); // status
                $this->applyListValidation($sheet, column: 'J', lookupColumn: 'A', allowBlank: true); // maintenance_city_name
            },
        ];
    }

    private function applyListValidation(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $column, string $lookupColumn, bool $allowBlank): void
    {
        $startRow = 2; // row 1 is headings
        $endRow = self::TEMPLATE_ROWS_WITH_VALIDATION + 1;

        $lookupStart = 2; // Lookups row 1 is headings
        $lookupEnd = 2000;
        $formula = "Lookups!\${$lookupColumn}\${$lookupStart}:\${$lookupColumn}\${$lookupEnd}";

        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank($allowBlank);
        $validation->setShowDropDown(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('قيمة غير صحيحة');
        $validation->setError('اختر قيمة من القائمة.');
        $validation->setPromptTitle('اختيار من القائمة');
        $validation->setPrompt('اختر قيمة من القائمة.');
        $validation->setFormula1($formula);

        for ($row = $startRow; $row <= $endRow; $row++) {
            $cell = "{$column}{$row}";
            $sheet->getCell($cell)->setDataValidation(clone $validation);
        }
    }
}

