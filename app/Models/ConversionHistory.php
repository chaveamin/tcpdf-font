<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversionHistory extends Model
{
    const STATUS_PENDING    = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED  = 'completed';
    const STATUS_FAILED     = 'failed';

    protected $fillable = [
        'visitor_id', 'font_names', 'zip_path', 'file_count',
        'status', 'error_message',
    ];

    public function getFullPathAttribute(): string
    {
        return storage_path('app/' . $this->zip_path);
    }

    public function isPending(): bool    { return $this->status === self::STATUS_PENDING; }
    public function isProcessing(): bool { return $this->status === self::STATUS_PROCESSING; }
    public function isCompleted(): bool  { return $this->status === self::STATUS_COMPLETED; }
    public function isFailed(): bool     { return $this->status === self::STATUS_FAILED; }

    public function markAs(string $status): void
    {
        $this->update(['status' => $status]);
    }

    public function markFailed(string $message): void
    {
        $this->update(['status' => self::STATUS_FAILED, 'error_message' => $message]);
    }
}
