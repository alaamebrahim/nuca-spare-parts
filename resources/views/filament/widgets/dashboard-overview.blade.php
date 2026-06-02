<div class="space-y-10" dir="rtl">

    <!-- SECTION 1 -->
    <section class="space-y-5">
        <h3 class="text-center text-xl font-semibold text-gray-800">
            المهمات التي لا تحتاج لصيانة أو تم عمل صيانة لها
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <!-- Card -->
            <div class="stat-card">
                <div class="stat-icon bg-blue-50 text-blue-600">
                    <x-filament::icon icon="heroicon-o-currency-dollar" class="w-5 h-5" />
                </div>
                <div class="stat-content">
                    <p class="stat-label">إجمالي التكلفة في حالة شراء جديد (بالمليون)</p>
                    <p class="stat-value text-blue-600">
                        {{ number_format(($noMaintenance['purchase_total'] ?? 0) / 1000000, 2) }}
                    </p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-amber-50 text-amber-600">
                    <x-filament::icon icon="heroicon-o-wrench" class="w-5 h-5" />
                </div>
                <div class="stat-content">
                    <p class="stat-label">إجمالي تكاليف الصيانة (بالمليون)</p>
                    <p class="stat-value text-amber-600">
                        {{ number_format(($noMaintenance['maintenance_total'] ?? 0) / 1000000, 2) }}
                    </p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-emerald-50 text-emerald-600">
                    <x-filament::icon icon="heroicon-o-sparkles" class="w-5 h-5" />
                </div>
                <div class="stat-content">
                    <p class="stat-label">التوفير (بالمليون)</p>
                    <p class="stat-value text-emerald-600">
                        {{ number_format(($noMaintenance['savings'] ?? 0) / 1000000, 2) }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 2 -->
    <section class="space-y-5">
        <h3 class="text-center text-xl font-semibold text-gray-800">
            المهمات التي تم الاستفادة بها وتركيبها بالفعل
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="stat-card">
                <div class="stat-icon bg-blue-50 text-blue-600">
                    <x-filament::icon icon="heroicon-o-currency-dollar" class="w-5 h-5" />
                </div>
                <div class="stat-content">
                    <p class="stat-label">إجمالي التكلفة في حالة شراء جديد (بالمليون)</p>
                    <p class="stat-value text-blue-600">
                        {{ number_format(($installed['purchase_total'] ?? 0) / 1000000, 2) }}
                    </p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-amber-50 text-amber-600">
                    <x-filament::icon icon="heroicon-o-wrench-screwdriver" class="w-5 h-5" />
                </div>
                <div class="stat-content">
                    <p class="stat-label">إجمالي تكاليف الصيانة (بالمليون)</p>
                    <p class="stat-value text-amber-600">
                        {{ number_format(($installed['maintenance_total'] ?? 0) / 1000000, 2) }}
                    </p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon bg-emerald-50 text-emerald-600">
                    <x-filament::icon icon="heroicon-o-chart-bar" class="w-5 h-5" />
                </div>
                <div class="stat-content">
                    <p class="stat-label">التوفير (بالمليون)</p>
                    <p class="stat-value text-emerald-600">
                        {{ number_format(($installed['savings'] ?? 0) / 1000000, 2) }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 3 -->
    <section class="space-y-5">
        <h3 class="text-center text-xl font-semibold text-gray-800">
            المهمات بحاجة لفحص وصيانة
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div class="stat-card ">
                <div class="stat-icon bg-blue-50 text-blue-600">
                    <x-filament::icon icon="heroicon-o-currency-dollar" class="w-5 h-5" />
                </div>
                <div class="stat-content">
                    <p class="stat-label">إجمالي القيمة في حالة شراء جديد (بالمليون)</p>
                    <p class="stat-value text-blue-600">
                        {{ number_format(($needsMaintenance['purchase_total'] ?? 0) / 1000000, 2) }}
                    </p>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-gray-500">
            آخر تحديث {{ $now->translatedFormat('Y-m-d H:i') }}
        </p>
    </section>
</div>
