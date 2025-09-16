<?php

namespace St693ava\FilamentEmailManager\Filament\Resources\EmailTemplateLayoutResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use St693ava\FilamentEmailManager\Filament\Resources\EmailTemplateLayoutResource;

class CreateEmailTemplateLayout extends CreateRecord
{
    protected static string $resource = EmailTemplateLayoutResource::class;

    protected ?string $heading = ' ';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If this is set as default, remove default from others
        if ($data['is_default'] ?? false) {
            static::$resource::getModel()::where('is_default', true)
                ->update(['is_default' => false]);
        }

        // Validate that wrapper_html contains {{content}} placeholder
        if (!str_contains($data['wrapper_html'] ?? '', '{{content}}')) {
            throw new \Exception('Wrapper HTML must contain {{content}} placeholder');
        }

        return $data;
    }
}