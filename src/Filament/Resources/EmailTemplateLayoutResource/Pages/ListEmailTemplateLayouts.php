<?php

namespace St693ava\FilamentEmailManager\Filament\Resources\EmailTemplateLayoutResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use St693ava\FilamentEmailManager\Filament\Resources\EmailTemplateLayoutResource;

class ListEmailTemplateLayouts extends ListRecords
{
    protected static string $resource = EmailTemplateLayoutResource::class;

    protected ?string $heading = ' ';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}