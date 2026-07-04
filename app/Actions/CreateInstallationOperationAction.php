<?php

namespace App\Actions;

use App\Data\InstallationOperations\CreateInstallationOperationInputData;
use App\Models\InstallationOperation;
use App\Models\SparePart;
use Illuminate\Support\Facades\DB;

class CreateInstallationOperationAction
{
    public static function run(CreateInstallationOperationInputData $data): InstallationOperation
    {
        return DB::transaction(function () use ($data) {
            $sparePart = SparePart::findOrFail($data->spare_part_id);

            return InstallationOperation::create([
                'spare_part_id' => $sparePart->id,
                'examine_city_id' => $sparePart->city_id,
                'beneficiary_city_id' => $data->beneficiary_city_id,
                'quantity' => $data->quantity,
                'installation_date' => $data->installation_date,
                'description' => $data->description,
                'notes' => $data->notes,
            ]);
        });
    }
}
