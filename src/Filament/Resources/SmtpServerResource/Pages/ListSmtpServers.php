<?php

namespace St693ava\FilamentEmailManager\Filament\Resources\SmtpServerResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use St693ava\FilamentEmailManager\Filament\Resources\SmtpServerResource;

class ListSmtpServers extends ListRecords
{
    protected static string $resource = SmtpServerResource::class;

    protected ?string $heading = ' ';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}