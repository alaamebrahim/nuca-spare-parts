<?php

namespace App\Models;

use App\Enums\BackupStatusEnum;
use App\Enums\BackupTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

class Backup extends Model
{
    protected $fillable = [
        'name',
        'filename',
        'path',
        'disk',
        'status',
        'type',
        'size',
        'error_message',
        'user_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => BackupStatusEnum::class,
            'type' => BackupTypeEnum::class,
            'size' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Backup $backup): void {
            if (blank($backup->path)) {
                return;
            }

            Storage::disk($backup->storageDisk())->delete($backup->path);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function storageDisk(): string
    {
        return $this->disk ?: config('backup.disk', 'r2');
    }

    public function humanSize(): string
    {
        if ($this->size === null) {
            return '—';
        }

        return Number::fileSize($this->size);
    }

    public function isDownloadable(): bool
    {
        return $this->status === BackupStatusEnum::Completed
            && filled($this->path)
            && Storage::disk($this->storageDisk())->exists($this->path);
    }

    public function downloadUrl(): ?string
    {
        if (! $this->isDownloadable()) {
            return null;
        }

        $disk = Storage::disk($this->storageDisk());
        $minutes = (int) config('backup.download_url_minutes', 60);
        $driver = config('filesystems.disks.'.$this->storageDisk().'.driver');

        if ($driver === 's3') {
            return $disk->temporaryUrl($this->path, now()->addMinutes($minutes));
        }

        return $disk->url($this->path);
    }
}
