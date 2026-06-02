<?php

use App\DataProcessors\SparePartsDataProcessor;
use App\Models\City;
use App\Models\SparePart;
use App\Models\SparePartCategory;
use App\Models\SparePartType;

it('computes totals correctly', function () {
    $city = City::create(['name' => 'A']);
    $type = SparePartType::create(['name' => 'T']);
    $category = SparePartCategory::create(['name' => 'C']);
    $sp = SparePart::create([
        'city_id' => $city->id,
        'type_id' => $type->id,
        'category_id' => $category->id,
        'quantity' => 3,
        'estimated_cost' => 10,
        'maintenance_cost' => 2,
    ]);

    expect(SparePartsDataProcessor::estimatedTotal($sp))->toBe(30);
    expect(SparePartsDataProcessor::maintenanceTotal($sp))->toBe(6);
});

it('provides option labels with available quantity', function () {
    $city = City::create(['name' => 'B']);
    $type = SparePartType::create(['name' => 'Type']);
    $category = SparePartCategory::create(['name' => 'Cat']);
    $sp = SparePart::create([
        'city_id' => $city->id,
        'type_id' => $type->id,
        'category_id' => $category->id,
        'quantity' => 5,
        'estimated_cost' => 1,
    ]);

    $label = SparePartsDataProcessor::labelForOption($sp);
    expect($label)->toContain('Type')
        ->and($label)->toContain('Cat')
        ->and($label)->toContain('متاح:');
});