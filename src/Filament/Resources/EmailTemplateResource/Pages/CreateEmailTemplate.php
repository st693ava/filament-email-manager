<?php

namespace St693ava\FilamentEmailManager\Filament\Resources\EmailTemplateResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use St693ava\FilamentEmailManager\Filament\Resources\EmailTemplateResource;

class CreateEmailTemplate extends CreateRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected ?string $heading = ' ';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}