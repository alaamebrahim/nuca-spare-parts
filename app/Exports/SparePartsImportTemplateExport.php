<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SparePartsImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new SparePartsImportTemplateSheet(),
            new SparePartsImportLookupsSheet(),
        ];
    }
}

