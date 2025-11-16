<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Models\DocumentType;
use Filament\Forms\Get;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get as UtilitiesGet;
use Filament\Schemas\Components\Utilities\Set as UtilitiesSet;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    Select::make('city_id')
                        ->label('المدينة')
                        ->preload()
                        ->searchable()
                        ->relationship('city', 'name')
                        ->required(),
                    Select::make('document_type_id')
                        ->label('نوع الملف')
                        ->preload()
                        ->searchable()
                        ->relationship('documentType', 'name')
                        ->default(1)
                        ->live()
                        // Reset the uploaded file when type changes to avoid stale validation
                        ->afterStateUpdated(fn(UtilitiesSet $set) => $set('file', null))
                        ->required(),
                    DatePicker::make('document_date')
                        ->label('تاريخ الملف')
                        ->required(),
                    Textarea::make('description')
                        ->default(null)
                        ->label('الوصف')
                        ->required()
                        ->columnSpanFull(),
                    FileUpload::make('file')
                        ->label('الملف')
                        // Remount on type change so OS dialog filter updates
                        ->id(fn(UtilitiesGet $get) => 'file-upload-' . ($get('document_type_id') ?? 'none'))
                        // Dynamically restrict file types based on selected document type name
                        ->acceptedFileTypes(fn(UtilitiesGet $get) => array_values(array_unique(array_merge(
                            self::acceptedTypesForDocumentType($get('document_type_id')),
                            self::acceptedMimeTypesForDocumentType($get('document_type_id')),
                        ))))
                        ->helperText(fn(UtilitiesGet $get) => self::acceptedTypesHint($get('document_type_id')))
                        ->reactive()
                        // Validate by both file extensions and MIME types
                        ->rules(fn(UtilitiesGet $get) => [
                            self::acceptedMimesRule($get('document_type_id')),
                            self::acceptedMimeTypesRule($get('document_type_id')),
                        ])
                        ->directory('documents/' . auth()->id() . '/' . now()->year . '/' . now()->month)
                        ->visibility('public')
                        ->required(),
                    Textarea::make('notes')
                        ->label('الملاحظات')
                        ->default(null)
                        ->columnSpanFull(),
                ])
                    ->columns(1)->columnSpanFull()
            ]);
    }

    /**
     * Map accepted MIME types based on the selected document type name.
     * If no type or unknown, return a broad default set to avoid blocking uploads.
     */
    private static function acceptedTypesForDocumentType(?int $documentTypeId): array
    {
        $typeName = optional(DocumentType::find($documentTypeId))->name;
        $name = mb_strtolower($typeName ?? '');

        // NOTE: Returning file extensions ensures Windows/macOS file dialog filters correctly.
        // Excel
        if ($name !== '' && (str_contains($name, 'excel') || str_contains($name, 'اكسل'))) {
            return ['.xlsx', '.xls', '.csv'];
        }

        // PDF
        if ($name !== '' && (str_contains($name, 'pdf') || str_contains($name, 'بي دي اف'))) {
            return ['.pdf'];
        }

        // Word
        if ($name !== '' && (str_contains($name, 'word') || str_contains($name, 'وورد'))) {
            return ['.doc', '.docx'];
        }

        // Images
        if ($name !== '' && (str_contains($name, 'image') || str_contains($name, 'صورة') || str_contains($name, 'صور') || str_contains($name, 'jpeg') || str_contains($name, 'png') || str_contains($name, 'jpg'))) {
            return ['.jpg', '.jpeg', '.png', '.webp'];
        }

        // PowerPoint
        if ($name !== '' && (str_contains($name, 'powerpoint') || str_contains($name, 'عرض تقديمي') || str_contains($name, 'ppt'))) {
            return ['.ppt', '.pptx'];
        }

        // Archives
        if ($name !== '' && (str_contains($name, 'zip') || str_contains($name, 'أرشيف'))) {
            return ['.zip'];
        }

        // Text
        if ($name !== '' && (str_contains($name, 'text') || str_contains($name, 'نصي'))) {
            return ['.txt'];
        }

        // Default: allow common office docs, images, pdf before a specific type is chosen
        return ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.csv', '.jpg', '.jpeg', '.png', '.webp'];
    }

    private static function acceptedTypesHint(?int $documentTypeId): string
    {
        $types = self::acceptedTypesForDocumentType($documentTypeId);

        // Map extensions to labels for a user-friendly hint
        $exts = collect($types)->map(function ($ext) {
            $e = ltrim(mb_strtolower($ext), '.');
            return match ($e) {
                'pdf' => 'PDF',
                'doc' => 'DOC',
                'docx' => 'DOCX',
                'xls' => 'XLS',
                'xlsx' => 'XLSX',
                'csv' => 'CSV',
                'jpg', 'jpeg' => 'JPG/JPEG',
                'png' => 'PNG',
                'webp' => 'WEBP',
                'ppt' => 'PPT',
                'pptx' => 'PPTX',
                'zip' => 'ZIP',
                'txt' => 'TXT',
                default => mb_strtoupper($e),
            };
        })->unique()->implode(', ');

        return 'الملفات المسموحة: ' . $exts;
    }

    /**
     * Create a dynamic Laravel mimes rule from the selected type.
     */
    private static function acceptedMimesRule(?int $documentTypeId): string
    {
        $exts = collect(self::acceptedTypesForDocumentType($documentTypeId))
            ->map(fn($ext) => ltrim(mb_strtolower($ext), '.'))
            ->unique()
            ->implode(',');

        if ($exts === '') {
            $exts = 'pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png,webp';
        }

        return 'mimes:' . $exts;
    }

    /**
     * MIME types list and rule builder to complement extensions validation.
     */
    private static function acceptedMimeTypesRule(?int $documentTypeId): string
    {
        $mimes = collect(self::acceptedMimeTypesForDocumentType($documentTypeId))
            ->unique()
            ->implode(',');

        if ($mimes === '') {
            $mimes = 'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,image/jpeg,image/png,image/webp,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation';
        }

        return 'mimetypes:' . $mimes;
    }

    /**
     * Map selected type to MIME types (used for server-side validation and extra safety).
     */
    private static function acceptedMimeTypesForDocumentType(?int $documentTypeId): array
    {
        $typeName = optional(DocumentType::find($documentTypeId))->name;
        $name = mb_strtolower($typeName ?? '');

        if ($name !== '' && (str_contains($name, 'excel') || str_contains($name, 'اكسل'))) {
            return [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-excel',
                'text/csv',
            ];
        }

        if ($name !== '' && (str_contains($name, 'pdf') || str_contains($name, 'بي دي اف'))) {
            return ['application/pdf'];
        }

        if ($name !== '' && (str_contains($name, 'word') || str_contains($name, 'وورد'))) {
            return [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ];
        }

        if ($name !== '' && (str_contains($name, 'image') || str_contains($name, 'صورة') || str_contains($name, 'صور') || str_contains($name, 'jpeg') || str_contains($name, 'png') || str_contains($name, 'jpg'))) {
            return [
                'image/jpeg',
                'image/png',
                'image/webp',
            ];
        }

        if ($name !== '' && (str_contains($name, 'powerpoint') || str_contains($name, 'عرض تقديمي') || str_contains($name, 'ppt'))) {
            return [
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ];
        }

        if ($name !== '' && (str_contains($name, 'zip') || str_contains($name, 'أرشيف'))) {
            return ['application/zip', 'application/x-zip-compressed'];
        }

        if ($name !== '' && (str_contains($name, 'text') || str_contains($name, 'نصي'))) {
            return ['text/plain'];
        }

        return [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'image/jpeg',
            'image/png',
            'image/webp',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
    }
}
