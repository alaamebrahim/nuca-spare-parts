<header
    class="dash-header"
    dir="rtl"
    x-data="{
        date: '',
        time: '',
        tick() {
            const now = new Date();
            this.date = new Intl.DateTimeFormat('ar-EG', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric',
            }).format(now);
            this.time = new Intl.DateTimeFormat('ar-EG', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            }).format(now);
        },
    }"
    x-init="tick(); setInterval(tick, 1000)"
>
    <div class="dash-header-title">
        <h1>لوحة التحكم</h1>
    </div>

    <div class="dash-header-meta" aria-live="polite">
        <span class="dash-header-date" x-text="date"></span>
        <span class="dash-header-time" x-text="time"></span>
    </div>
</header>
