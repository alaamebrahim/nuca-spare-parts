<?php

use App\Actions\SpareParts\SaveSparePartImportBatchAction;
use App\Actions\SpareParts\StageSparePartImportBatchAction;
use App\Enums\SparePartStatusEnum;
use App\Models\City;
use App\Models\SparePart;
use App\Models\SparePartCategory;
use App\Models\SparePartImportBatch;
use App\Models\SparePartImportRow;
use App\Models\SparePartType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function makeSparePartsImportXlsx(array $rows): string
{
    $headers = [
        'city_name',
        'type_name',
        'category_name',
        'location',
        'technical_description',
        'quantity',
        'status',
        'estimated_cost',
        'maintenance_cost',
        'maintenance_city_name',
    ];

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray([$headers, ...$rows], null, 'A1');

    $tmp = tempnam(sys_get_temp_dir(), 'spare-parts-import-');
    if ($tmp === false) {
        throw new RuntimeException('Failed to create temp file.');
    }
    $path = $tmp . '.xlsx';

    $writer = new Xlsx($spreadsheet);
    $writer->save($path);

    return $path;
}

it('stages rows and flags missing lookups', function () {
    City::create(['name' => 'القاهرة']);
    SparePartType::create(['name' => 'نوع موجود']);
    SparePartCategory::create(['name' => 'فئة موجودة']);

    $path = makeSparePartsImportXlsx([
        [
            'مدينة غير موجودة',
            'نوع موجود',
            'فئة موجودة',
            'مكان',
            'وصف',
            1,
            SparePartStatusEnum::New->value,
            10,
            0,
            null,
        ],
    ]);

    $batch = StageSparePartImportBatchAction::run(
        filePath: $path,
        originalFilename: 'test.xlsx',
        userId: null,
    );

    expect($batch)->toBeInstanceOf(SparePartImportBatch::class);
    expect(SparePartImportRow::query()->where('batch_id', $batch->id)->count())->toBe(1);

    $row = SparePartImportRow::query()->where('batch_id', $batch->id)->firstOrFail();
    expect($row->city_id)->toBeNull()
        ->and($row->type_id)->not->toBeNull()
        ->and($row->category_id)->not->toBeNull()
        ->and($row->has_errors)->toBeTrue()
        ->and($row->errors)->toHaveKey('city_name');
});

it('saves only when rows are valid and does not create related records', function () {
    $city = City::create(['name' => 'القاهرة']);
    $type = SparePartType::create(['name' => 'نوع موجود']);
    $category = SparePartCategory::create(['name' => 'فئة موجودة']);

    $path = makeSparePartsImportXlsx([
        [
            'القاهرة',
            'نوع موجود',
            'فئة موجودة',
            'مكان',
            'وصف',
            2,
            SparePartStatusEnum::New->value,
            5,
            null,
            null,
        ],
    ]);

    $batch = StageSparePartImportBatchAction::run(
        filePath: $path,
        originalFilename: 'test.xlsx',
        userId: null,
    );

    expect(City::count())->toBe(1)
        ->and(SparePartType::count())->toBe(1)
        ->and(SparePartCategory::count())->toBe(1)
        ->and(SparePart::count())->toBe(0);

    $createdCount = SaveSparePartImportBatchAction::run($batch->id);

    expect($createdCount)->toBe(1)
        ->and(SparePart::count())->toBe(1)
        ->and(SparePartImportBatch::findOrFail($batch->id)->status)->toBe('saved')
        ->and(City::count())->toBe(1)
        ->and(SparePartType::count())->toBe(1)
        ->and(SparePartCategory::count())->toBe(1);

    $sp = SparePart::firstOrFail();
    expect($sp->city_id)->toBe($city->id)
        ->and($sp->type_id)->toBe($type->id)
        ->and($sp->category_id)->toBe($category->id)
        ->and($sp->quantity)->toBe(2);
});

