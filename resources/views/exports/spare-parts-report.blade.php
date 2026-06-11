@extends('exports.layouts.reports')

@section('page_title', 'تقرير المهمات')
@section('report_title', 'تقرير المهمات')

@section('content')
    <table>
        <thead>
            <tr>
                <th style="width: 3%;">م</th>
                <th>المدينة</th>
                <th>مكان الفحص</th>
                <th>نوع المهمة</th>
                <th>الفئة</th>
                <th>الوصف الفني</th>
                <th>الكمية</th>
                <th>الحالة</th>
                <th>التكلفة التقديرية</th>
                <th>إجمالي التكلفة</th>
                <th>تكلفة الصيانة</th>
                <th>المدينة المنوطة بالصيانة</th>
                <th>إجمالي تكلفة الصيانة</th>
                <th>تاريخ الإضافة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $record->city?->name ?? '-' }}</td>
                    <td>{{ $record->location ?? '-' }}</td>
                    <td>{{ $record->type?->name ?? '-' }}</td>
                    <td>{{ $record->category?->name ?? '-' }}</td>
                    <td>{{ $record->technical_description ?? '-' }}</td>
                    <td>{{ $record->quantity }}</td>
                    <td>{{ \App\Enums\SparePartStatusEnum::from($record->status)->label() }}</td>
                    <td>{{ number_format($record->estimated_cost, 2) }}</td>
                    <td>{{ number_format(\App\DataProcessors\SparePartsDataProcessor::estimatedTotal($record), 2) }}</td>
                    <td>{{ number_format($record->maintenance_cost, 2) }}</td>
                    <td>{{ $record->maintenanceCity?->name ?? '-' }}</td>
                    <td>{{ number_format(\App\DataProcessors\SparePartsDataProcessor::maintenanceTotal($record), 2) }}</td>
                    <td>{{ $record->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14">لا توجد بيانات مطابقة للفلاتر المحددة</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
