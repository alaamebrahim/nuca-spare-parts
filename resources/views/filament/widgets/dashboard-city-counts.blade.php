<div class="dash-cities" dir="rtl" wire:loading.class="is-loading">
    <div class="dash-cities-head">
        <div>
            <h2 class="dash-panel-title">عدد المهمات لكل مدينة</h2>
            <p class="dash-cities-summary">
                إجمالي المدن: <strong>{{ number_format($totalCities) }}</strong>
                <span class="dash-sep" aria-hidden="true">·</span>
                إجمالي المهمات: <strong>{{ number_format($totalParts) }}</strong>
                <span class="dash-sep" aria-hidden="true">·</span>
                المعروض حاليا: <strong>{{ number_format($shownCount) }}</strong>
            </p>
        </div>
    </div>

    <div class="dash-cities-toolbar">
        <label class="dash-search">
            <span class="sr-only">ابحث عن مدينة</span>
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="dash-search-icon" />
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="ابحث عن مدينة..."
                autocomplete="off"
            />
        </label>

        <div class="dash-seg" role="group" aria-label="ترتيب المدن">
            <button
                type="button"
                wire:click="$set('sortBy', 'count_desc')"
                class="{{ $this->sortBy === 'count_desc' ? 'is-active' : '' }}"
            >
                حسب العدد
            </button>
            <button
                type="button"
                wire:click="$set('sortBy', 'name_asc')"
                class="{{ $this->sortBy === 'name_asc' ? 'is-active' : '' }}"
            >
                أبجدي
            </button>
        </div>

        <button
            type="button"
            wire:click="$toggle('expanded')"
            class="dash-ghost"
        >
            {{ $this->expanded ? 'إخفاء' : 'عرض الكل' }}
        </button>
    </div>

    <div class="dash-city-list" wire:loading.class="opacity-60">
        @forelse ($cityCounts as $city)
            <div class="dash-city-row" wire:key="city-{{ $city['id'] ?? $city['name'] }}">
                <div class="dash-city-meta">
                    <span class="dash-city-name">{{ $city['name'] }}</span>
                    <span class="dash-city-count">{{ number_format($city['count']) }}</span>
                </div>
                <div class="dash-city-track" aria-hidden="true">
                    <span
                        class="dash-city-bar"
                        style="width: {{ $maxCount > 0 ? round(($city['count'] / $maxCount) * 100) : 0 }}%"
                    ></span>
                </div>
            </div>
        @empty
            <div class="dash-empty">
                @if ($hasSearch)
                    لا توجد مدن مطابقة لبحثك
                @else
                    لا توجد بيانات مدن حالياً
                @endif
            </div>
        @endforelse
    </div>
</div>
