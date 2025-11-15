<div class="w-full">
    <div class="relative overflow-hidden rounded-xl bg-slate-900 p-6 text-white shadow-md">
        <div x-data="{
                date: '',
                time: '',
                tick() {
                    const now = new Date();
                    this.date = new Intl.DateTimeFormat('ar-EG', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' }).format(now);
                    this.time = new Intl.DateTimeFormat('ar-EG', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).format(now);
                }
            }"
            x-init="tick(); setInterval(tick, 1000)"
            class="flex flex-col items-center text-center gap-2">
            <x-filament::icon icon="heroicon-o-clock" class="w-16 h-16 mb-3 opacity-90" />
            <p class="text-base font-medium" x-text="date"></p>
            <p class="text-3xl font-bold tracking-wider" x-text="time"></p>
        </div>
    </div>
</div>