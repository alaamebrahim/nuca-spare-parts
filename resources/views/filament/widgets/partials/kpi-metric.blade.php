<div class="dash-metric {{ ($emphasized ?? false) ? 'is-emphasized' : '' }} is-{{ $variant ?? 'purchase' }}">
    <div class="dash-metric-icon" aria-hidden="true">
        <x-filament::icon :icon="$icon" class="w-5 h-5" />
    </div>
    <p class="dash-metric-value">{{ $value }}</p>
    <p class="dash-metric-unit">{{ $unit ?? 'مليون' }}</p>
    <p class="dash-metric-label">{{ $label }}</p>
</div>
