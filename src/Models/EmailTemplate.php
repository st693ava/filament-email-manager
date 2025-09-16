<?php

namespace St693ava\FilamentEmailManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'content_html',
        'content_text',
        'layout_id',
        'placeholders',
        'merge_tags',
        'default_values',
        'is_active',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'merge_tags' => 'array',
        'default_values' => 'array',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
        'placeholders' => '[]',
        'merge_tags' => '[]',
        'default_values' => '[]',
    ];

    public function layout(): BelongsTo
    {
        return $this->belongsTo(EmailTemplateLayout::class, 'layout_id');
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class, 'template_id');
    }

    public function emailQueue(): HasMany
    {
        return $this->hasMany(EmailQueue::class, 'template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    public function getPlaceholderNames(): array
    {
        return collect($this->placeholders)->pluck('name')->toArray();
    }

    public function getRequiredPlaceholders(): array
    {
        return collect($this->placeholders)
            ->filter(fn($placeholder) => $placeholder['required'] ?? false)
            ->pluck('name')
            ->toArray();
    }

    public function processContent(array $data = []): string
    {
        // Merge default values with provided data
        $mergeData = array_merge($this->default_values ?? [], $data);

        $content = $this->content_html;

        // Replace placeholders with data
        foreach ($mergeData as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }

        // Apply layout if exists
        if ($this->layout) {
            $content = $this->layout->renderLayout($content, $mergeData);
        }

        return $content;
    }

    public function processSubject(array $data = []): string
    {
        // Merge default values with provided data
        $mergeData = array_merge($this->default_values ?? [], $data);

        $subject = $this->subject;

        // Replace placeholders with data
        foreach ($mergeData as $key => $value) {
            $subject = str_replace("{{" . $key . "}}", $value, $subject);
        }

        return $subject;
    }

    public function processTextContent(array $data = []): ?string
    {
        if (empty($this->content_text)) {
            return null;
        }

        // Merge default values with provided data
        $mergeData = array_merge($this->default_values ?? [], $data);

        $content = $this->content_text;

        // Replace placeholders with data
        foreach ($mergeData as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }

        return $content;
    }

    public function validateData(array $data): array
    {
        $errors = [];
        $required = $this->getRequiredPlaceholders();

        foreach ($required as $placeholder) {
            if (!isset($data[$placeholder]) || empty($data[$placeholder])) {
                $errors[] = "Missing required placeholder: {$placeholder}";
            }
        }

        return $errors;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });

        static::updating(function ($template) {
            if ($template->isDirty('name') && empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    protected static function newFactory()
    {
        return \St693ava\FilamentEmailManager\Database\Factories\EmailTemplateFactory::new();
    }
}