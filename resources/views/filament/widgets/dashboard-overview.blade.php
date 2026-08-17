<div class="dash-kpis" dir="rtl">
    <section class="dash-group dash-group-pair" aria-label="ملخص الفحص">
        <div class="dash-group-column">
            <h2 id="dash-group-inspected" class="dash-group-title">
                إجمالي ما تم فحصه
            </h2>

            <div class="dash-metric-grid dash-metric-grid--single">
                @include('filament.widgets.partials.kpi-metric', [
                    'label' => 'إجمالي التكلفة في حالة شراء جديد',
                    'value' => number_format(($inspected['purchase_total'] ?? 0) / 1000000, 2),
                    'variant' => 'purchase',
                    'icon' => 'heroicon-o-clipboard-document-check',
                    'emphasized' => true,
                ])
            </div>
        </div>

        <div class="dash-group-column">
            <h2 id="dash-group-pending" class="dash-group-title">
                المهمات بحاجة لفحص وصيانة
            </h2>

            <div class="dash-metric-grid dash-metric-grid--single">
                @include('filament.widgets.partials.kpi-metric', [
                    'label' => 'إجمالي القيمة في حالة شراء جديد',
                    'value' => number_format(($needsMaintenance['purchase_total'] ?? 0) / 1000000, 2),
                    'variant' => 'pending',
                    'icon' => 'heroicon-o-exclamation-triangle',
                    'emphasized' => false,
                ])
            </div>
        </div>
    </section>

    <section class="dash-group" aria-labelledby="dash-group-ready">
        <h2 id="dash-group-ready" class="dash-group-title">
            المهمات التي لا تحتاج لصيانة أو تم عمل صيانة لها
        </h2>

        <div class="dash-metric-grid">
            @include('filament.widgets.partials.kpi-metric', [
                'label' => 'إجمالي التكلفة في حالة شراء جديد',
                'value' => number_format(($noMaintenance['purchase_total'] ?? 0) / 1000000, 2),
                'variant' => 'purchase',
                'icon' => 'heroicon-o-banknotes',
                'emphasized' => false,
            ])

            @include('filament.widgets.partials.kpi-metric', [
                'label' => 'إجمالي تكاليف الصيانة',
                'value' => number_format(($noMaintenance['maintenance_total'] ?? 0) / 1000000, 2),
                'variant' => 'maintenance',
                'icon' => 'heroicon-o-wrench',
                'emphasized' => false,
            ])

            @include('filament.widgets.partials.kpi-metric', [
                'label' => 'التوفير',
                'value' => number_format(($noMaintenance['savings'] ?? 0) / 1000000, 2),
                'variant' => 'savings',
                'icon' => 'heroicon-o-sparkles',
                'emphasized' => true,
            ])
        </div>
    </section>

    <section class="dash-group" aria-labelledby="dash-group-installed">
        <h2 id="dash-group-installed" class="dash-group-title">
            المهمات التي تم الاستفادة بها وتركيبها بالفعل
        </h2>

        <div class="dash-metric-grid">
            @include('filament.widgets.partials.kpi-metric', [
                'label' => 'إجمالي التكلفة في حالة شراء جديد',
                'value' => number_format(($installed['purchase_total'] ?? 0) / 1000000, 2),
                'variant' => 'purchase',
                'icon' => 'heroicon-o-cube',
                'emphasized' => false,
            ])

            @include('filament.widgets.partials.kpi-metric', [
                'label' => 'إجمالي تكاليف الصيانة',
                'value' => number_format(($installed['maintenance_total'] ?? 0) / 1000000, 2),
                'variant' => 'maintenance',
                'icon' => 'heroicon-o-wrench-screwdriver',
                'emphasized' => false,
            ])

            @include('filament.widgets.partials.kpi-metric', [
                'label' => 'التوفير',
                'value' => number_format(($installed['savings'] ?? 0) / 1000000, 2),
                'variant' => 'savings',
                'icon' => 'heroicon-o-chart-bar',
                'emphasized' => true,
            ])
        </div>
    </section>

    <p class="dash-updated">
        آخر تحديث {{ $now->translatedFormat('Y-m-d H:i') }}
    </p>
</div>
