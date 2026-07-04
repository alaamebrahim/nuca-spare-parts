<?php

namespace App\Actions\SpareParts;

use App\DataProcessors\SparePartImportRowsDataProcessor;
use App\Models\SparePart;
use App\Models\SparePartImportBatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SaveSparePartImportBatchAction
{
    public static function run(int $batchId): int
    {
        try {
            return DB::transaction(function () use ($batchId) {
                $batch = SparePartImportBatch::query()
                    ->whereKey($batchId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($batch->status !== 'draft') {
                    throw new RuntimeException('لا يمكن حفظ هذا الاستيراد لأنه ليس في حالة مسودة.');
                }

                $rows = $batch->rows()->lockForUpdate()->get();

                foreach ($rows as $row) {
                    SparePartImportRowsDataProcessor::recalculate($row);
                    $row->refresh();

                    if ($row->has_errors) {
                        throw new RuntimeException('لا يمكن الحفظ قبل تصحيح جميع الأخطاء في البيانات المستوردة.');
                    }
                }

                $created = 0;

                foreach ($rows as $row) {
                    SparePart::create([
                        'city_id' => $row->city_id,
                        'type_id' => $row->type_id,
                        'category_id' => $row->category_id,
                        'location' => $row->location_raw,
                        'technical_description' => $row->technical_description_raw,
                        'quantity' => $row->quantity ?? 0,
                        'status' => $row->status,
                        'estimated_cost' => $row->estimated_cost ?? 0,
                        'maintenance_cost' => $row->maintenance_cost ?? 0,
                        'maintenance_city_id' => $row->maintenance_city_id,
                    ]);

                    $created++;
                }

                $batch->update(['status' => 'saved']);

                return $created;
            });
        } catch (Throwable $e) {
            Log::error('Failed to save spare part import batch', [
                'exception' => $e,
                'batch_id' => $batchId,
            ]);

            throw $e;
        }
    }
}

