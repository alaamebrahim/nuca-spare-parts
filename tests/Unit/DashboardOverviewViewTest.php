<?php

use Illuminate\Support\Carbon;

it('renders existing dashboard kpi values in millions', function () {
    $html = view('filament.widgets.dashboard-overview', [
        'noMaintenance' => [
            'purchase_total' => 1_061_440_000,
            'maintenance_total' => 4_780_000,
            'savings' => 1_056_670_000,
        ],
        'installed' => [
            'purchase_total' => 788_050_000,
            'maintenance_total' => 5_420_000,
            'savings' => 782_630_000,
        ],
        'needsMaintenance' => [
            'purchase_total' => 401_560_000,
        ],
        'now' => Carbon::parse('2026-08-14 23:13:00'),
    ])->render();

    expect($html)
        ->toContain('المهمات التي لا تحتاج لصيانة أو تم عمل صيانة لها')
        ->toContain('المهمات التي تم الاستفادة بها وتركيبها بالفعل')
        ->toContain('المهمات بحاجة لفحص وصيانة')
        ->toContain('إجمالي التكلفة في حالة شراء جديد')
        ->toContain('إجمالي تكاليف الصيانة')
        ->toContain('التوفير')
        ->toContain('إجمالي القيمة في حالة شراء جديد')
        ->toContain('1,061.44')
        ->toContain('4.78')
        ->toContain('1,056.67')
        ->toContain('788.05')
        ->toContain('5.42')
        ->toContain('782.63')
        ->toContain('401.56')
        ->toContain('آخر تحديث');
});
