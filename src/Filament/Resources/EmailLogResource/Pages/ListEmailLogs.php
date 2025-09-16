<?php

namespace St693ava\FilamentEmailManager\Filament\Resources\EmailLogResource\Pages;

use Filament\Resources\Pages\ListRecords;
use St693ava\FilamentEmailManager\Filament\Resources\EmailLogResource;

class ListEmailLogs extends ListRecords
{
    protected static string $resource = EmailLogResource::class;

    protected ?string $heading = ' ';

    protected function getHeaderActions(): array
    {
        return [
            // No create action for read-only resource
        ];
    }
}