<?php

namespace App\Actions\SpareParts;

use App\Models\City;
use App\Models\SparePartCategory;
use App\Models\SparePartImportBatch;
use App\Models\SparePartImportRow;
use App\Models\SparePartType;
use App\Services\SpareParts\SparePartExcelImportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class StageSparePartImportBatchAction
{
    public static function run(
        string $filePath,
        ?string $originalFilename,
        ?int $userId,
        ?int $existingBatchId = null,
    ): SparePartImportBatch {
        try {
            return DB::transaction(function () use ($filePath, $originalFilename, $userId, $existingBatchId) {
                $batch = $existingBatchId
                    ? SparePartImportBatch::query()->whereKey($existingBatchId)->lockForUpdate()->firstOrFail()
                    : SparePartImportBatch::create([
                        'user_id' => $userId,
                        'status' => 'draft',
                        'original_filename' => $originalFilename,
                    ]);

                if ($batch->status !== 'draft') {
                    $batch->status = 'draft';
                }
                $batch->original_filename = $originalFilename;
                $batch->user_id ??= $userId;
                $batch->save();

                $batch->rows()->delete();

                $service = new SparePartExcelImportService();
                $rows = $service->parse($filePath);

                foreach ($rows as $row) {
                    $errors = $row->errors;

                    $cityId = self::matchCityId($row->city_name, $errors, 'city_name');
                    $typeId = self::matchTypeId($row->type_name, $errors, 'type_name');
                    $categoryId = self::matchCategoryId($row->category_name, $errors, 'category_name');
                    $maintenanceCityId = self::matchCityId($row->maintenance_city_name, $errors, 'maintenance_city_name');

                    $hasErrors = ! empty($errors);

                    SparePartImportRow::create([
                        'batch_id' => $batch->id,

                        'city_name_raw' => $row->city_name,
                        'type_name_raw' => $row->type_name,
                        'category_name_raw' => $row->category_name,
                        'maintenance_city_name_raw' => $row->maintenance_city_name,
                        'location_raw' => $row->location,
                        'technical_description_raw' => $row->technical_description,
                        'quantity_raw' => $row->quantity === null ? null : (string) $row->quantity,
                        'status_raw' => $row->status,
                        'estimated_cost_raw' => $row->estimated_cost === null ? null : (string) $row->estimated_cost,
                        'maintenance_cost_raw' => $row->maintenance_cost === null ? null : (string) $row->maintenance_cost,

                        'city_id' => $cityId,
                        'type_id' => $typeId,
                        'category_id' => $categoryId,
                        'maintenance_city_id' => $maintenanceCityId,

                        'quantity' => $row->quantity,
                        'status' => $row->status,
                        'estimated_cost' => $row->estimated_cost,
                        'maintenance_cost' => $row->maintenance_cost,

                        'has_errors' => $hasErrors,
                        'errors' => $errors,
                    ]);
                }

                return $batch->refresh();
            });
        } catch (Throwable $e) {
            Log::error('Failed to stage spare part import batch', [
                'exception' => $e,
                'original_filename' => $originalFilename,
                'user_id' => $userId,
                'existing_batch_id' => $existingBatchId,
            ]);

            throw $e;
        }
    }

    /**
     * @param  array<string, string>  $errors
     */
    private static function matchCityId(?string $name, array &$errors, string $fieldKey): ?int
    {
        if (! filled($name)) {
            return null;
        }

        $matches = City::query()->where('name', $name)->pluck('id');

        if ($matches->count() === 1) {
            return (int) $matches->first();
        }

        $errors[$fieldKey] = $matches->count() > 1
            ? 'يوجد أكثر من تطابق — اختر القيمة الصحيحة'
            : 'غير موجودة — اختر قيمة موجودة';

        return null;
    }

    /**
     * @param  array<string, string>  $errors
     */
    private static function matchTypeId(?string $name, array &$errors, string $fieldKey): ?int
    {
        if (! filled($name)) {
            return null;
        }

        $matches = SparePartType::query()->where('name', $name)->pluck('id');

        if ($matches->count() === 1) {
            return (int) $matches->first();
        }

        $errors[$fieldKey] = $matches->count() > 1
            ? 'يوجد أكثر من تطابق — اختر القيمة الصحيحة'
            : 'غير موجود — اختر قيمة موجودة';

        return null;
    }

    /**
     * @param  array<string, string>  $errors
     */
    private static function matchCategoryId(?string $name, array &$errors, string $fieldKey): ?int
    {
        if (! filled($name)) {
            return null;
        }

        $matches = SparePartCategory::query()->where('name', $name)->pluck('id');

        if ($matches->count() === 1) {
            return (int) $matches->first();
        }

        $errors[$fieldKey] = $matches->count() > 1
            ? 'يوجد أكثر من تطابق — اختر القيمة الصحيحة'
            : 'غير موجودة — اختر قيمة موجودة';

        return null;
    }
}

