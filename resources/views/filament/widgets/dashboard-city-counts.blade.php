<div class="space-y-3">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <h3 class="text-sm font-semibold text-slate-700">عدد المهمات لكل مدينة</h3>
        <div class="text-xs text-slate-600">
            إجمالي المدن: <span class="font-semibold">{{ number_format($totalCities) }}</span>
            • إجمالي المهمات: <span class="font-semibold">{{ number_format($totalParts) }}</span>
            • المعروض حالياً: <span class="font-semibold">{{ number_format($shownCount) }}</span>
        </div>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex items-center gap-2">
            <input type="text" wire:model.debounce.300ms="search" placeholder="ابحث عن مدينة..."
                class="w-full md:w-72 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <div class="flex rounded-lg overflow-hidden border border-slate-300">
                <button type="button" wire:click="$set('sortBy', 'count_desc')"
                    class="px-3 py-2 text-sm {{ $this->sortBy === 'count_desc' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700' }}">حسب
                    العدد</button>
                <button type="button" wire:click="$set('sortBy', 'name_asc')"
                    class="px-3 py-2 text-sm {{ $this->sortBy === 'name_asc' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700' }}">أبجدي</button>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" wire:click="$toggle('expanded')"
                class="rounded-lg bg-emerald-600 text-white px-4 py-2 text-sm">
                {{ $this->expanded ? 'إخفاء' : 'عرض الكل' }}
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3">
        @forelse ($cityCounts as $city)
            <div class="rounded-xl bg-indigo-600 text-white p-4 shadow-sm flex flex-col items-center text-center">
                <x-filament::icon icon="heroicon-o-building-office-2" class="w-14 h-14 mb-2 opacity-95" />
                <p class="text-sm opacity-90">{{ $city['name'] }}</p>
                <p class="mt-1 text-2xl font-bold">{{ number_format($city['count']) }}</p>
            </div>
        @empty
            <div class="col-span-full">
                <div class="rounded-xl bg-slate-200 p-4 text-center text-slate-700">لا توجد بيانات مدن حالياً</div>
            </div>
        @endforelse
    </div>
</div>
