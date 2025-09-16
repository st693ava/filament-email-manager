<?php

namespace St693ava\FilamentEmailManager\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use St693ava\FilamentEmailManager\Models\EmailLog;

class EmailLogResource extends Resource
{
    protected static ?string $model = EmailLog::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-envelope-open';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 4;


    public static function getNavigationLabel(): string
    {
        return __('filament-email-manager::filament-email-manager.email_logs.title');
    }

    public static function getModelLabel(): string
    {
        return __('filament-email-manager::filament-email-manager.email_logs.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-email-manager::filament-email-manager.email_logs.plural');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('to_recipients')
                    ->label('To')
                    ->formatStateUsing(function ($state) {
                        $recipients = is_string($state) ? json_decode($state, true) : $state;
                        return is_array($recipients) ? implode(', ', array_slice($recipients, 0, 2)) . (count($recipients) > 2 ? '...' : '') : $state;
                    })
                    ->limit(30),

                Tables\Columns\TextColumn::make('smtpServer.name')
                    ->label('SMTP Server')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'sent' => 'Sent',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                    ]),

                Tables\Filters\SelectFilter::make('smtp_server_id')
                    ->label('SMTP Server')
                    ->relationship('smtpServer', 'name'),
            ])
            ->actions([
                Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => \St693ava\FilamentEmailManager\Filament\Resources\EmailLogResource\Pages\ListEmailLogs::route('/'),
            'view' => \St693ava\FilamentEmailManager\Filament\Resources\EmailLogResource\Pages\ViewEmailLog::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'sent')->count();
    }
}