<x-filament-panels::page>
    <div class="space-y-4">
        <x-filament::section>
            <form wire:submit="stageImport">
                <div class="mt-2">
                    {{ $this->form }}
                </div>

                <div class="mt-4 flex justify-end">
                    <x-filament::button type="submit" color="primary" icon="heroicon-o-arrow-up-tray">
                        استيراد الملف
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        <div class="mt-6">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>

