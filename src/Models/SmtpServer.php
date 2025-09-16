<?php

namespace St693ava\FilamentEmailManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmtpServer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_email',
        'from_name',
        'rate_limit_per_hour',
        'settings',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'settings' => 'array',
        'password' => 'encrypted',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'port' => 'integer',
        'rate_limit_per_hour' => 'integer',
    ];

    protected $attributes = [
        'port' => 587,
        'encryption' => 'tls',
        'rate_limit_per_hour' => 0,
        'is_active' => true,
        'is_default' => false,
    ];

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    public function emailQueue(): HasMany
    {
        return $this->hasMany(EmailQueue::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function isUnlimited(): bool
    {
        return $this->rate_limit_per_hour <= 0;
    }

    public function getRemainingRateLimit(): int
    {
        if ($this->isUnlimited()) {
            return -1; // Unlimited
        }

        $sentLastHour = $this->emailLogs()
            ->where('status', 'sent')
            ->where('sent_at', '>=', now()->subHour())
            ->count();

        return max(0, $this->rate_limit_per_hour - $sentLastHour);
    }

    public function canSendEmail(): bool
    {
        return $this->is_active && ($this->isUnlimited() || $this->getRemainingRateLimit() > 0);
    }

    protected static function newFactory()
    {
        return \St693ava\FilamentEmailManager\Database\Factories\SmtpServerFactory::new();
    }
}