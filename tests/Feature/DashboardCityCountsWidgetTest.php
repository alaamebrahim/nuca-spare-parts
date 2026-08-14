<?php

use App\Filament\Widgets\DashboardCityCountsWidget;
use App\Models\City;
use App\Models\SparePart;
use App\Models\SparePartCategory;
use App\Models\SparePartType;
use Livewire\Livewire;

it('keeps city counts, search, and sorting on the dashboard widget', function () {
    $type = SparePartType::create(['name' => 'نوع']);
    $category = SparePartCategory::create(['name' => 'فئة']);
    $october = City::create(['name' => 'حدائق أكتوبر']);
    $cairo = City::create(['name' => 'القاهرة الجديدة']);

    SparePart::create([
        'city_id' => $october->id,
        'type_id' => $type->id,
        'category_id' => $category->id,
        'quantity' => 1,
    ]);
    SparePart::create([
        'city_id' => $october->id,
        'type_id' => $type->id,
        'category_id' => $category->id,
        'quantity' => 1,
    ]);
    SparePart::create([
        'city_id' => $cairo->id,
        'type_id' => $type->id,
        'category_id' => $category->id,
        'quantity' => 1,
    ]);

    Livewire::test(DashboardCityCountsWidget::class)
        ->assertSee('عدد المهمات لكل مدينة')
        ->assertSee('إجمالي المدن')
        ->assertSee('إجمالي المهمات')
        ->assertSee('المعروض حاليا')
        ->assertSee('حدائق أكتوبر')
        ->assertSee('القاهرة الجديدة')
        ->assertSee('2')
        ->assertSee('1')
        ->set('search', 'أكتوبر')
        ->assertSee('حدائق أكتوبر')
        ->assertDontSee('القاهرة الجديدة')
        ->set('search', 'مدينة غير موجودة')
        ->assertSee('لا توجد مدن مطابقة لبحثك')
        ->set('search', '')
        ->set('sortBy', 'name_asc')
        ->assertSet('sortBy', 'name_asc')
        ->assertSee('القاهرة الجديدة')
        ->assertSee('حدائق أكتوبر');
});
