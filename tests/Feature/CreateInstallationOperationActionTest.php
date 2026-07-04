<?php

use App\Actions\CreateInstallationOperationAction;
use App\Data\InstallationOperations\CreateInstallationOperationInputData;
use App\Models\City;
use App\Models\InstallationOperation;
use App\Models\SparePart;
use App\Models\SparePartCategory;
use App\Models\SparePartType;
use Illuminate\Support\Carbon;

it('creates installation operation', function () {
    $examineCity = City::create(['name' => 'Ex']);
    $beneficiaryCity = City::create(['name' => 'Ben']);
    $type = SparePartType::create(['name' => 'T']);
    $category = SparePartCategory::create(['name' => 'C']);
    $sp = SparePart::create([
        'city_id' => $examineCity->id,
        'type_id' => $type->id,
        'category_id' => $category->id,
        'quantity' => 10,
        'estimated_cost' => 1,
    ]);

    $input = CreateInstallationOperationInputData::from([
        'spare_part_id' => $sp->id,
        'beneficiary_city_id' => $beneficiaryCity->id,
        'quantity' => 2,
        'installation_date' => Carbon::now()->format('Y-m-d'),
        'description' => 'd',
        'notes' => 'n',
    ]);

    $op = CreateInstallationOperationAction::run($input);
    expect($op)->toBeInstanceOf(InstallationOperation::class)
        ->and($op->spare_part_id)->toBe($sp->id)
        ->and($op->examine_city_id)->toBe($examineCity->id)
        ->and($op->beneficiary_city_id)->toBe($beneficiaryCity->id)
        ->and($op->quantity)->toBe(2);
});
