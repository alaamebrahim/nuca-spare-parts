<?php

namespace App\Filament\Widgets;

use App\Models\SparePart;
use Filament\Widgets\Widget;

class DashboardCityCountsWidget extends Widget
{
    protected string $view = 'filament.widgets.dashboard-city-counts';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 12,
        'xl' => 4,
    ];

    protected static ?int $sort = 3;

    // Reactive UI state
    public string $search = '';

    public string $sortBy = 'count_desc'; // options: count_desc, name_asc

    public bool $expanded = false;

    public int $perPage = 12; // default visible cards when collapsed

    protected function getViewData(): array
    {
        $rows = SparePart::query()
            ->selectRaw('city_id, COUNT(*) as parts_count')
            ->groupBy('city_id')
            ->with('city')
            ->get();

        $cityCounts = $rows->map(function ($row) {
            return [
                'id' => $row->city_id,
                'name' => optional($row->city)->name ?? 'بدون مدينة',
                'count' => (int) $row->parts_count,
            ];
        });

        // Filter by city name (case-insensitive, works with Arabic)
        if ($this->search !== '') {
            $needle = $this->search;
            $cityCounts = $cityCounts->filter(fn ($c) => mb_stripos($c['name'], $needle) !== false);
        }

        // Sort by selected option
        if ($this->sortBy === 'name_asc') {
            $cityCounts = $cityCounts->sortBy(fn ($c) => $c['name'], SORT_NATURAL | SORT_FLAG_CASE)->values();
        } else { // count_desc
            $cityCounts = $cityCounts->sortByDesc('count')->values();
        }

        $totalCities = $cityCounts->count();
        $totalParts = $cityCounts->sum('count');
        $maxCount = max(1, (int) $cityCounts->max('count'));

        // Collapse to top N unless expanded or filtered by search (show all when searching)
        $visibleCounts = ($this->expanded || $this->search !== '')
            ? $cityCounts
            : $cityCounts->take($this->perPage);

        return [
            'cityCounts' => $visibleCounts,
            'totalCities' => $totalCities,
            'totalParts' => $totalParts,
            'shownCount' => $visibleCounts->count(),
            'maxCount' => $maxCount,
            'hasSearch' => $this->search !== '',
        ];
    }
}
