<div class="space-y-6">
    <!-- Basic Information -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-primary-600 mb-4">المعلومات الأساسية</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-secondary-600">المدينة التي تم الفحص بها</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->city->name ?? 'غير محدد' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-600">نوع المهمة</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->type->name ?? 'غير محدد' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-600">الفئة</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->category->name ?? 'غير محدد' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-600">الكمية</label>
                <p class="mt-1 text-sm text-gray-900">{{ number_format($record->quantity) }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-600">الحالة</label>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($record->status === 'Maintained') bg-green-100 text-green-800
                    @elseif($record->status === 'UsedNeedsMaintainance') bg-yellow-100 text-yellow-800
                    @elseif($record->status === 'UsedInGoodState') bg-blue-100 text-blue-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ \App\Enums\SparePartStatusEnum::from($record->status)->label() }}
                </span>
            </div>
        </div>
    </div>

    <!-- Location and Description -->
    @if($record->location || $record->technical_description)
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-primary-600 mb-4">الوصف والموقع</h3>
        <div class="space-y-4">
            @if($record->location)
            <div>
                <label class="block text-sm font-medium text-secondary-600">مكان الفحص</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->location }}</p>
            </div>
            @endif
            @if($record->technical_description)
            <div>
                <label class="block text-sm font-medium text-secondary-600">الوصف الفني</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->technical_description }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Cost Information -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-primary-600 mb-4">معلومات التكلفة</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-secondary-600">التكلفة التقديرية للوحدة</label>
                <p class="mt-1 text-sm text-gray-900">{{ number_format($record->estimated_cost, 2) }} ج.م</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-600">الكمية</label>
                <p class="mt-1 text-sm text-gray-900">{{ number_format($record->quantity) }}</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-secondary-600">التكلفة الإجمالية التقديرية</label>
                <p class="mt-1 text-lg font-semibold text-primary-600">{{ number_format($record->estimated_cost *
                    $record->quantity, 2) }} ج.م</p>
            </div>
        </div>
    </div>

    <!-- Maintenance Information -->
    @if($record->status === 'Maintained' && ($record->maintenance_city_id || $record->maintenance_cost))
    <div class="bg-green-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-primary-600 mb-4">معلومات الصيانة</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($record->maintenance_city_id)
            <div>
                <label class="block text-sm font-medium text-secondary-600">المدينة المنوطة بالصيانة</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->maintenanceCity->name ?? 'غير محدد' }}</p>
            </div>
            @endif
            @if($record->maintenance_cost && $record->maintenance_cost > 0)
            <div>
                <label class="block text-sm font-medium text-secondary-600">تكلفة الصيانة للوحدة</label>
                <p class="mt-1 text-sm text-gray-900">{{ number_format($record->maintenance_cost, 2) }} ج.م</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-600">الكمية</label>
                <p class="mt-1 text-sm text-gray-900">{{ number_format($record->quantity) }}</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-secondary-600">التكلفة الإجمالية للصيانة</label>
                <p class="mt-1 text-lg font-semibold text-green-600">{{ number_format($record->maintenance_cost *
                    $record->quantity, 2) }} ج.م</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Cost Summary -->
    @if($record->status === 'Maintained' && $record->maintenance_cost && $record->maintenance_cost > 0)
    <div class="bg-blue-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-primary-600 mb-4">ملخص التكاليف</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-secondary-600">التكلفة الإجمالية التقديرية</label>
                <p class="mt-1 text-lg font-semibold text-primary-600">{{ number_format($record->estimated_cost *
                    $record->quantity, 2) }} ج.م</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-600">التكلفة الإجمالية للصيانة</label>
                <p class="mt-1 text-lg font-semibold text-green-600">{{ number_format($record->maintenance_cost *
                    $record->quantity, 2) }} ج.م</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-600">إجمالي التكاليف</label>
                <p class="mt-1 text-xl font-bold text-blue-600">{{ number_format(($record->estimated_cost +
                    $record->maintenance_cost) * $record->quantity, 2) }} ج.م</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Installation Operations -->
    @if($record->installationOperations->count() > 0)
    <div class="bg-blue-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-primary-600 mb-4">عمليات النقل والتركيب</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            مدينة الفحص</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المدينة المستفيدة</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الكمية</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            تاريخ التركيب</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($record->installationOperations as $operation)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $operation->examineCity->name
                            ?? 'غير محدد' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                            $operation->beneficiaryCity->name ?? 'غير محدد' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                            number_format($operation->quantity) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                            $operation->installation_date->format('Y-m-d') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $operation->notes ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Timestamps -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-semibold text-primary-600 mb-4">معلومات النظام</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-secondary-600">تاريخ الإضافة</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->created_at->format('Y-m-d H:i:s') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-600">تاريخ آخر تحديث</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->updated_at->format('Y-m-d H:i:s') }}</p>
            </div>
        </div>
    </div>
</div>