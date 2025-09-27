<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::section>
            <x-slot name="heading">
                فلاتر التقرير
            </x-slot>

            <x-slot name="description">
                استخدم الفلاتر أدناه لتخصيص تقرير المهمات حسب احتياجاتك
            </x-slot>

            <div class="mt-6">
                {{ $this->form }}
            </div>
        </x-filament::section>

        <div style="margin: 30px"></div>
        {{-- Results Table --}}
        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>