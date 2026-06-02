<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::section>
            <x-slot name="heading">
                فلاتر تقرير عمليات التركيب
            </x-slot>
            
            <x-slot name="description">
                استخدم الفلاتر أدناه لتخصيص تقرير عمليات التركيب حسب احتياجاتك
            </x-slot>

            <div class="mt-6">
                {{ $this->form }}
            </div>
        </x-filament::section>

        {{-- Results Table --}}
        <x-filament::section>
            <x-slot name="heading">
                نتائج تقرير عمليات التركيب
            </x-slot>

            <div class="mt-6">
                {{ $this->table }}
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
