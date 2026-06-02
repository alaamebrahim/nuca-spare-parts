<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Document $document): void {
            $document->user_id = auth()->id();
            $document->disk ??= config('filesystems.documents_disk', 'r2');
        });

        static::deleting(function (Document $document): void {
            if (blank($document->file)) {
                return;
            }

            Storage::disk($document->storageDisk())->delete($document->file);
        });
    }

    public function storageDisk(): string
    {
        return $this->disk ?? config('filesystems.documents_disk', 'r2');
    }

    public function fileUrl(): ?string
    {
        if (blank($this->file)) {
            return null;
        }

        $disk = Storage::disk($this->storageDisk());

        if (config('filesystems.disks.'.$this->storageDisk().'.visibility') === 'private') {
            return $disk->temporaryUrl($this->file, now()->addHour());
        }

        return $disk->url($this->file);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
