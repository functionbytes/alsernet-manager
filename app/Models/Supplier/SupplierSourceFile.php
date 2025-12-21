<?php

namespace App\Models\Supplier;

use App\Library\Traits\HasUid;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SupplierSourceFile extends Model
{
    use HasUid;

    protected $fillable = [
        'source_id',
        'batch_id',
        'original_filename',
        'stored_path',
        'file_size_bytes',
        'mime_type',
        'file_hash',
        'status',
        'rows_total',
        'rows_processed',
        'rows_success',
        'rows_failed',
        'processing_started_at',
        'processing_completed_at',
        'processing_error',
        'upload_type',
        'uploaded_by',
        'delete_after_processed',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size_bytes' => 'integer',
            'rows_total' => 'integer',
            'rows_processed' => 'integer',
            'rows_success' => 'integer',
            'rows_failed' => 'integer',
            'processing_started_at' => 'datetime',
            'processing_completed_at' => 'datetime',
            'delete_after_processed' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(SupplierSource::class, 'source_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SupplierExtractionBatch::class, 'batch_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeByUploadType($query, string $type)
    {
        return $query->where('upload_type', $type);
    }

    public function scopeManualUploads($query)
    {
        return $query->where('upload_type', 'manual');
    }

    public function scopeFtpUploads($query)
    {
        return $query->where('upload_type', 'ftp');
    }

    public function scopeEmailUploads($query)
    {
        return $query->where('upload_type', 'email');
    }

    public function scopeApiUploads($query)
    {
        return $query->where('upload_type', 'api');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'processing_started_at' => now(),
        ]);
    }

    public function markAsProcessed(): void
    {
        $this->update([
            'status' => 'processed',
            'processing_completed_at' => now(),
        ]);

        if ($this->delete_after_processed) {
            $this->deleteFile();
        }
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'processing_error' => $error,
            'processing_completed_at' => now(),
        ]);
    }

    public function updateProgress(int $processed, int $success, int $failed): void
    {
        $this->update([
            'rows_processed' => $processed,
            'rows_success' => $success,
            'rows_failed' => $failed,
        ]);
    }

    public function getProgressPercentage(): float
    {
        if (! $this->rows_total || $this->rows_total === 0) {
            return 0;
        }

        return round(($this->rows_processed / $this->rows_total) * 100, 2);
    }

    public function getSuccessRate(): float
    {
        if (! $this->rows_processed || $this->rows_processed === 0) {
            return 0;
        }

        return round(($this->rows_success / $this->rows_processed) * 100, 2);
    }

    public function getFileSizeFormatted(): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->file_size_bytes;

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    public function getFileContents(): string
    {
        return Storage::get($this->stored_path);
    }

    public function fileExists(): bool
    {
        return Storage::exists($this->stored_path);
    }

    public function deleteFile(): bool
    {
        if (Storage::exists($this->stored_path)) {
            Storage::delete($this->stored_path);
        }

        $this->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);

        return true;
    }

    public function downloadUrl(): string
    {
        return Storage::url($this->stored_path);
    }

    public static function findByHash(string $hash): ?self
    {
        return static::where('file_hash', $hash)->first();
    }

    public static function calculateHash(string $filePath): string
    {
        return hash_file('sha256', $filePath);
    }
}
