<?php

use App\Enums\SparePartStatusEnum;
use App\Models\City;
use App\Models\SparePart;
use App\Models\SparePartCategory;
use App\Models\SparePartType;
use App\Traits\SparePartsBaseQueries;

function createSparePartForSearch(array $attributes = []): SparePart
{
    $city = $attributes['city'] ?? City::create(['name' => 'مدينة افتراضية']);
    $maintenanceCity = $attributes['maintenanceCity'] ?? null;
    $type = $attributes['type'] ?? SparePartType::create(['name' => 'نوع افتراضي']);
    $category = $attributes['category'] ?? SparePartCategory::create(['name' => 'فئة افتراضية']);

    return SparePart::create([
        'city_id' => $city->id,
        'maintenance_city_id' => $maintenanceCity?->id,
        'type_id' => $type->id,
        'category_id' => $category->id,
        'location' => $attributes['location'] ?? null,
        'technical_description' => $attributes['technical_description'] ?? 'وصف افتراضي',
        'quantity' => $attributes['quantity'] ?? 1,
        'status' => $attributes['status'] ?? SparePartStatusEnum::New->value,
        'estimated_cost' => $attributes['estimated_cost'] ?? 0,
        'maintenance_cost' => $attributes['maintenance_cost'] ?? 0,
    ]);
}

it('finds spare parts by related examine city name', function () {
    $matching = createSparePartForSearch([
        'city' => City::create(['name' => 'الرياض']),
    ]);
    $other = createSparePartForSearch([
        'city' => City::create(['name' => 'جدة']),
    ]);

    $ids = SparePartsBaseQueries::applySearch(SparePart::query(), 'الرياض')->pluck('id');

    expect($ids)->toContain($matching->id)
        ->and($ids)->not->toContain($other->id);
});

it('finds spare parts by related maintenance city name', function () {
    $matching = createSparePartForSearch([
        'maintenanceCity' => City::create(['name' => 'الدمام']),
    ]);
    $other = createSparePartForSearch([
        'maintenanceCity' => City::create(['name' => 'الخبر']),
    ]);

    $ids = SparePartsBaseQueries::applySearch(SparePart::query(), 'الدمام')->pluck('id');

    expect($ids)->toContain($matching->id)
        ->and($ids)->not->toContain($other->id);
});

it('finds spare parts by type, category, location and description', function () {
    $byType = createSparePartForSearch([
        'type' => SparePartType::create(['name' => 'مضخة']),
    ]);
    $byCategory = createSparePartForSearch([
        'category' => SparePartCategory::create(['name' => 'كهرباء']),
    ]);
    $byLocation = createSparePartForSearch([
        'location' => 'مستودع الشمال',
    ]);
    $byDescription = createSparePartForSearch([
        'technical_description' => 'فلتر هواء صناعي',
    ]);
    $unrelated = createSparePartForSearch();

    expect(SparePartsBaseQueries::applySearch(SparePart::query(), 'مضخة')->pluck('id'))
        ->toContain($byType->id)
        ->not->toContain($unrelated->id);

    expect(SparePartsBaseQueries::applySearch(SparePart::query(), 'كهرباء')->pluck('id'))
        ->toContain($byCategory->id)
        ->not->toContain($unrelated->id);

    expect(SparePartsBaseQueries::applySearch(SparePart::query(), 'مستودع')->pluck('id'))
        ->toContain($byLocation->id)
        ->not->toContain($unrelated->id);

    expect(SparePartsBaseQueries::applySearch(SparePart::query(), 'فلتر')->pluck('id'))
        ->toContain($byDescription->id)
        ->not->toContain($unrelated->id);
});

it('finds spare parts by arabic status label', function () {
    $matching = createSparePartForSearch([
        'status' => SparePartStatusEnum::New->value,
    ]);
    $other = createSparePartForSearch([
        'status' => SparePartStatusEnum::Maintained->value,
    ]);

    $ids = SparePartsBaseQueries::applySearch(SparePart::query(), 'جديد')->pluck('id');

    expect($ids)->toContain($matching->id)
        ->and($ids)->not->toContain($other->id);
});
