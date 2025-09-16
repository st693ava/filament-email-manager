<?php

namespace St693ava\FilamentEmailManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailQueue extends Model
{
    use HasFactory;

    protected $table = 'email_queue';

    protected $fillable = [
        'smtp_server_id',
        'template_id',
        'recipients',
        'data',
        'attachments',
        'priority',
        'scheduled_at',
        'attempts',
        'last_attempt_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'recipients' => 'array',
        'data' => 'array',
        'attachments' => 'array',
        'priority' => 'integer',
        'attempts' => 'integer',
        'scheduled_at' => 'datetime',
        'last_attempt_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
        'priority' => 0,
        'attempts' => 0,
        'recipients' => '{"to":[],"cc":[],"bcc":[]}',
        'data' => '{}',
        'attachments' => '[]',
    ];

    public function smtpServer(): BelongsTo
    {
        return $this->belongsTo(SmtpServer::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            });
    }

    public function scopeHighPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    public function scopeOldestFirst($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    public function getAllRecipients(): array
    {
        $recipients = [];

        foreach (['to', 'cc', 'bcc'] as $type) {
            if (!empty($this->recipients[$type])) {
                $recipients = array_merge($recipients, $this->recipients[$type]);
            }
        }

        return array_unique($recipients);
    }

    public function getRecipientsCount(): int
    {
        return count($this->getAllRecipients());
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isReady(): bool
    {
        return $this->isPending() &&
               (is_null($this->scheduled_at) || $this->scheduled_at <= now());
    }

    public function markAsProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'last_attempt_at' => now(),
        ]);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
        ]);
    }

    public function markAsFailed(string $error = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'attempts' => $this->attempts + 1,
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update([
            'status' => 'cancelled',
        ]);
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
        $this->update(['last_attempt_at' => now()]);
    }

    protected static function newFactory()
    {
        return \St693ava\FilamentEmailManager\Database\Factories\EmailQueueFactory::new();
    }
}