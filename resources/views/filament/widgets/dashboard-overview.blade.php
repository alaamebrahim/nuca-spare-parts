<div class="w-full grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="rounded-xl p-4 text-white shadow-sm bg-blue-600">
        <div class="flex flex-col items-center text-center">
            <x-filament::icon icon="heroicon-o-currency-dollar" class="w-16 h-16 mb-2 opacity-95" />
            <p class="text-sm opacity-90">اجمالي التكاليف
                في حالة شراء
                جديد</p>
            <p class="mt-1 text-2xl font-bold">{{ number_format($purchaseTotal, 2) }} ج.م</p>
            <p class="mt-2 text-[11px] opacity-80">آخر تحديث {{ $now->translatedFormat('Y-m-d H:i') }}</p>
        </div>
    </div>

    <div class="rounded-xl p-4 text-white shadow-sm bg-amber-500">
        <div class="flex flex-col items-center text-center">
            <x-filament::icon icon="heroicon-o-wrench" class="w-16 h-16 mb-2 opacity-95" />
            <p class="text-sm opacity-90">إجمالي تكاليف الصيانة</p>
            <p class="mt-1 text-2xl font-bold">{{ number_format($maintenanceTotal, 2) }} ج.م</p>
            <p class="mt-2 text-[11px] opacity-80">آخر تحديث {{ $now->translatedFormat('Y-m-d H:i') }}</p>
        </div>
    </div>

    <div class="rounded-xl p-4 text-white shadow-sm bg-emerald-600">
        <div class="flex flex-col items-center text-center">
            <x-filament::icon icon="heroicon-o-chart-bar" class="w-16 h-16 mb-2 opacity-95" />
            <p class="text-sm opacity-90">التوفير</p>
            <p class="mt-1 text-2xl font-bold">{{ number_format($savings, 2) }} ج.م</p>
            <p class="mt-2 text-[11px] opacity-80">آخر تحديث {{ $now->translatedFormat('Y-m-d H:i') }}</p>
        </div>
    </div>
</div>
