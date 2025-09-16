<?php

namespace St693ava\FilamentEmailManager\Filament\Resources\SmtpServerResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use St693ava\FilamentEmailManager\Filament\Resources\SmtpServerResource;

class EditSmtpServer extends EditRecord
{
    protected static string $resource = SmtpServerResource::class;

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

        return $data;
    }
}