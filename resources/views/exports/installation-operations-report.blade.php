@extends('exports.layouts.reports')

@section('page_title', 'تقرير عمليات التركيب')
@section('report_title', 'تقرير عمليات التركيب')

@section('content')
    <table>
        <thead>
            <tr>
                <th style="width: 3%;">م</th>
                <th>نوع المهمة</th>
                <th>فئة المهمة</th>
                <th>الوصف الفني</th>
                <th>مدينة الفحص</th>
                <th>مدينة المستفيد</th>
                <th>الكمية</th>
                <th>تاريخ التركيب</th>
                <th>حالة التركيب</th>
                <th>كيفية الاستفادة</th>
                <th>الملاحظات</th>
                <th>تاريخ الإضافة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $record->sparePart?->type?->name ?? '-' }}</td>
                    <td>{{ $record->sparePart?->category?->name ?? '-' }}</td>
                    <td>{{ $record->sparePart?->technical_description ?? '-' }}</td>
                    <td>{{ $record->examineCity?->name ?? '-' }}</td>
                    <td>{{ $record->beneficiaryCity?->name ?? '-' }}</td>
                    <td>{{ $record->quantity }}</td>
                    <td>{{ $record->installation_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ \App\Enums\InstallationStatusEnum::from($record->status)->label() }}</td>
                    <td>{{ $record->description ?? '-' }}</td>
                    <td>{{ $record->notes ?? '-' }}</td>
                    <td>{{ $record->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12">لا توجد بيانات مطابقة للفلاتر المحددة</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
