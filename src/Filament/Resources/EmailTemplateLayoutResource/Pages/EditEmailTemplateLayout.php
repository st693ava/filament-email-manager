<?php

namespace St693ava\FilamentEmailManager\Filament\Resources\EmailTemplateLayoutResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use St693ava\FilamentEmailManager\Filament\Resources\EmailTemplateLayoutResource;

class EditEmailTemplateLayout extends EditRecord
{
    protected static string $resource = EmailTemplateLayoutResource::class;

    protected ?string $heading = ' ';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If this is set as default, remove default from others
        if ($data['is_default'] ?? false) {
            static::$resource::getModel()::where('is_default', true)
                ->where('id', '!=', $this->record->id)
                ->update(['is_default' => false]);
        }

        // Validate that wrapper_html contains {{content}} placeholder
        if (!str_contains($data['wrapper_html'] ?? '', '{{content}}')) {
            throw new \Exception('Wrapper HTML must contain {{content}} placeholder');
        }

        return $data;
    }
}