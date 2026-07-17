<x-filament-panels::page>
    @php
        $metrics = $this->getMetrics();
        $schedules = $this->getSchedules();
    @endphp

    <div class="space-y-8" dir="rtl">
        <div class="rounded-2xl border border-primary-100 bg-gradient-to-l from-primary-50 via-white to-secondary-50 p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-primary-700">إدارة النسخ الاحتياطي</p>
                    <h2 class="text-2xl font-semibold text-gray-900">
                        حماية بياناتك بنسخ احتياطية احترافية
                    </h2>
                    <p class="max-w-2xl text-sm leading-6 text-gray-600">
                        أنشئ نسخاً يدوية أو اعتمد على النسخ التلقائية اليومية والأسبوعية والشهرية. جميع النسخ تُحفظ على Cloudflare وتظهر في نفس السجل.
                    </p>
                </div>

                <div class="rounded-xl border border-white/70 bg-white/80 px-4 py-3 text-sm text-gray-600 shadow-sm backdrop-blur">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-cloud" class="h-5 w-5 text-primary-600" />
                        <span>التخزين: Cloudflare R2 · المجلد: <code class="text-xs">backups/database</code></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <div class="stat-card">
                <div class="stat-icon bg-primary-50 text-primary-700">
                    <x-filament::icon icon="heroicon-o-circle-stack" class="h-5 w-5" />
                </div>
                <div class="stat-content">
                    <p class="stat-label">إجمالي النسخ</p>
                    <p class="stat-value text-primary-700">{{ number_format($metrics['total']) }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-emerald-50 text-emerald-600">
                    <x-filament::icon icon="heroicon-o-check-circle" class="h-5 w-5" />
                </div>
                <div class="stat-content">
                    <p class="stat-label">نسخ مكتملة</p>
                    <p class="stat-value text-emerald-600">{{ number_format($metrics['completed']) }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-amber-50 text-amber-600">
                    <x-filament::icon icon="heroicon-o-server-stack" class="h-5 w-5" />
                </div>
                <div class="stat-content">
                    <p class="stat-label">الحجم الإجمالي</p>
                    <p class="stat-value text-amber-600">{{ $metrics['total_size_human'] }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-sky-50 text-sky-600">
                    <x-filament::icon icon="heroicon-o-clock" class="h-5 w-5" />
                </div>
                <div class="stat-content">
                    <p class="stat-label">آخر نسخة ناجحة</p>
                    <p class="stat-value !text-lg text-sky-600">
                        {{ $metrics['last_backup_at'] ?? 'لا يوجد' }}
                    </p>
                </div>
            </div>
        </div>

        <x-filament::section>
            <x-slot name="heading">
                الجداول التلقائية
            </x-slot>

            <x-slot name="description">
                النسخ اليومية والأسبوعية والشهرية تظهر تلقائياً في السجل أدناه
            </x-slot>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                @foreach ($schedules as $schedule)
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-base font-semibold text-gray-900">نسخ {{ $schedule['label'] }}</p>
                                <p class="mt-1 text-sm text-gray-500">{{ $schedule['schedule_label'] }}</p>
                            </div>
                            <span @class([
                                'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                'bg-emerald-50 text-emerald-700' => $schedule['enabled'],
                                'bg-gray-100 text-gray-500' => ! $schedule['enabled'],
                            ])>
                                {{ $schedule['enabled'] ? 'مفعّل' : 'متوقف' }}
                            </span>
                        </div>

                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500">التشغيل القادم</dt>
                                <dd class="font-medium text-gray-900">{{ $schedule['next_run_at'] ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500">آخر تشغيل</dt>
                                <dd class="font-medium text-gray-900">{{ $schedule['last_run_at'] ?? 'لا يوجد' }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500">آخر حالة</dt>
                                <dd class="font-medium text-gray-900">{{ $schedule['last_status'] ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        @if ($metrics['failed'] > 0)
            <div class="rounded-xl border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-700">
                يوجد {{ number_format($metrics['failed']) }} نسخة فاشلة. يمكنك حذفها وإعادة المحاولة.
            </div>
        @endif

        <x-filament::section>
            <x-slot name="heading">
                سجل النسخ الاحتياطية
            </x-slot>

            <x-slot name="description">
                جميع النسخ اليدوية والتلقائية في مكان واحد
            </x-slot>

            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
