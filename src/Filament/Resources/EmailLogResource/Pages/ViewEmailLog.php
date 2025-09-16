<?php

namespace St693ava\FilamentEmailManager\Filament\Resources\EmailLogResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use St693ava\FilamentEmailManager\Filament\Resources\EmailLogResource;

class ViewEmailLog extends ViewRecord
{
    protected static string $resource = EmailLogResource::class;

    protected ?string $heading = ' ';

    protected function getHeaderActions(): array
    {
        return [
            // No edit action for read-only resource
        ];
    }
}