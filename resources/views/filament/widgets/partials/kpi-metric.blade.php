<div class="dash-metric {{ ($emphasized ?? false) ? 'is-emphasized' : '' }} is-{{ $variant ?? 'purchase' }}">
    <p class="dash-metric-value">{{ $value }}</p>
    <p class="dash-metric-unit">{{ $unit ?? 'مليون' }}</p>
    <p class="dash-metric-label">
        <span class="dash-metric-icon" aria-hidden="true">
            <x-filament::icon :icon="$icon" class="w-3.5 h-3.5" />
        </span>
        {{ $label }}
    </p>
</div>
