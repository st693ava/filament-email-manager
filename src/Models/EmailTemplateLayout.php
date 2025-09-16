<?php

namespace St693ava\FilamentEmailManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTemplateLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'header_html',
        'footer_html',
        'wrapper_html',
        'css_styles',
        'settings',
        'is_default',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_default' => 'boolean',
    ];

    protected $attributes = [
        'is_default' => false,
        'wrapper_html' => '<!DOCTYPE html><html><head><meta charset="utf-8"><style>{{css}}</style></head><body>{{header}}{{content}}{{footer}}</body></html>',
    ];

    public function emailTemplates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class, 'layout_id');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function renderLayout(string $content, array $variables = []): string
    {
        $html = $this->wrapper_html;

        // Replace layout placeholders
        $html = str_replace('{{content}}', $content, $html);
        $html = str_replace('{{header}}', $this->header_html ?? '', $html);
        $html = str_replace('{{footer}}', $this->footer_html ?? '', $html);
        $html = str_replace('{{css}}', $this->css_styles ?? '', $html);

        // Replace any additional variables
        foreach ($variables as $key => $value) {
            $html = str_replace("{{" . $key . "}}", $value, $html);
        }

        return $html;
    }

    public function hasRequiredPlaceholders(): bool
    {
        return str_contains($this->wrapper_html, '{{content}}');
    }

    protected static function newFactory()
    {
        return \St693ava\FilamentEmailManager\Database\Factories\EmailTemplateLayoutFactory::new();
    }
}