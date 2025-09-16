<?php

namespace St693ava\FilamentEmailManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'smtp_server_id',
        'template_id',
        'recipients',
        'subject',
        'body_html',
        'body_text',
        'attachments',
        'headers',
        'metadata',
        'status',
        'sent_at',
        'failed_at',
        'error_message',
        'eml_file_path',
    ];

    protected $casts = [
        'recipients' => 'array',
        'attachments' => 'array',
        'headers' => 'array',
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
        'recipients' => '{"to":[],"cc":[],"bcc":[]}',
        'attachments' => '[]',
        'headers' => '[]',
        'metadata' => '[]',
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

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
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

    public function getAttachmentsCount(): int
    {
        return count($this->attachments ?? []);
    }

    public function getTotalAttachmentsSize(): int
    {
        return collect($this->attachments ?? [])
            ->sum('size');
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'sent';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSending(): bool
    {
        return $this->status === 'sending';
    }

    public function markAsSending(): void
    {
        $this->update([
            'status' => 'sending',
        ]);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'failed_at' => null,
            'error_message' => null,
        ]);
    }

    public function markAsFailed(string $error = null): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $error,
        ]);
    }

    public function hasEmlFile(): bool
    {
        return !empty($this->eml_file_path) && Storage::exists($this->eml_file_path);
    }

    public function getEmlFileSize(): ?int
    {
        if (!$this->hasEmlFile()) {
            return null;
        }

        return Storage::size($this->eml_file_path);
    }

    public function downloadEmlFile(): ?\Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!$this->hasEmlFile()) {
            return null;
        }

        return Storage::download(
            $this->eml_file_path,
            "email-{$this->id}.eml"
        );
    }

    protected static function newFactory()
    {
        return \St693ava\FilamentEmailManager\Database\Factories\EmailLogFactory::new();
    }
}