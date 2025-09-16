<?php

namespace St693ava\FilamentEmailManager\Filament\Resources\SmtpServerResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use St693ava\FilamentEmailManager\Filament\Resources\SmtpServerResource;

class CreateSmtpServer extends CreateRecord
{
    protected static string $resource = SmtpServerResource::class;

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

        return $data;
    }
}