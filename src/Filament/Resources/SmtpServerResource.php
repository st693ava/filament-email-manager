<?php

namespace St693ava\FilamentEmailManager\Filament\Resources;

use Filament\Forms\Components as FormComponents;
use Filament\Schemas\Components as LayoutComponents;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use St693ava\FilamentEmailManager\Filament\Resources\SmtpServerResource\Pages;
use St693ava\FilamentEmailManager\Models\SmtpServer;
use St693ava\FilamentEmailManager\Services\MailConfigService;
use UnitEnum;

class SmtpServerResource extends Resource
{
    protected static ?string $model = SmtpServer::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-server';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    // naviation group
    protected static UnitEnum|string|null $navigationGroup = 'Emails';



    public static function getNavigationLabel(): string
    {
        return __('filament-email-manager::filament-email-manager.smtp_servers.title');
    }

    public static function getModelLabel(): string
    {
        return __('filament-email-manager::filament-email-manager.smtp_servers.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-email-manager::filament-email-manager.smtp_servers.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                LayoutComponents\Section::make(__('filament-email-manager::filament-email-manager.smtp_servers.sections.server_configuration'))
                    ->schema([
                        FormComponents\TextInput::make('name')
                            ->label(__('filament-email-manager::filament-email-manager.smtp_servers.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('filament-email-manager::filament-email-manager.smtp_servers.placeholders.name')),

                        FormComponents\TextInput::make('host')
                            ->label(__('filament-email-manager::filament-email-manager.smtp_servers.fields.host'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('filament-email-manager::filament-email-manager.smtp_servers.placeholders.host')),

                        FormComponents\Select::make('encryption')
                            ->label(__('filament-email-manager::filament-email-manager.smtp_servers.fields.encryption'))
                            ->options([
                                '' => __('filament-email-manager::filament-email-manager.smtp_servers.options.encryption_none'),
                                'tls' => __('filament-email-manager::filament-email-manager.smtp_servers.options.encryption_tls'),
                                'ssl' => __('filament-email-manager::filament-email-manager.smtp_servers.options.encryption_ssl'),
                            ])
                            ->default('tls')
                            ->live()
                            ->helperText(__('filament-email-manager::filament-email-manager.smtp_servers.help_text.encryption'))
                            ->afterStateUpdated(function (?string $state, callable $set) {
                                // Define as portas padrão baseadas na encriptação
                                $defaultPorts = [
                                    '' => 25,      // Sem encriptação
                                    'tls' => 587,  // TLS/STARTTLS
                                    'ssl' => 465,  // SSL/TLS
                                ];

                                // Se $state for null, usar string vazia como padrão
                                $encryptionType = $state ?? '';
                                $set('port', $defaultPorts[$encryptionType] ?? 587);
                            }),

                        FormComponents\TextInput::make('port')
                            ->label(__('filament-email-manager::filament-email-manager.smtp_servers.fields.port'))
                            ->required()
                            ->numeric()
                            ->default(587)
                            ->minValue(1)
                            ->maxValue(65535),

                        FormComponents\TextInput::make('username')
                            ->label(__('filament-email-manager::filament-email-manager.smtp_servers.fields.username'))
                            ->maxLength(255)
                            ->placeholder(__('filament-email-manager::filament-email-manager.smtp_servers.placeholders.username')),

                        FormComponents\TextInput::make('password')
                            ->label(__('filament-email-manager::filament-email-manager.smtp_servers.fields.password'))
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn($state) => filled($state) ? $state : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->placeholder(__('filament-email-manager::filament-email-manager.smtp_servers.placeholders.password')),
                    ])
                    ->columns(2),

                LayoutComponents\Section::make(__('filament-email-manager::filament-email-manager.smtp_servers.sections.sender_configuration'))
                    ->schema([
                        FormComponents\TextInput::make('from_email')
                            ->label(__('filament-email-manager::filament-email-manager.smtp_servers.fields.from_email'))
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->placeholder(__('filament-email-manager::filament-email-manager.smtp_servers.placeholders.from_email')),

                        FormComponents\TextInput::make('from_name')
                            ->label(__('filament-email-manager::filament-email-manager.smtp_servers.fields.from_name'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder(__('filament-email-manager::filament-email-manager.smtp_servers.placeholders.from_name')),
                    ])
                    ->columns(2),

                LayoutComponents\Section::make(__('filament-email-manager::filament-email-manager.smtp_servers.sections.rate_limiting'))
                    ->schema([
                        FormComponents\TextInput::make('rate_limit_per_hour')
                            ->label(__('filament-email-manager::filament-email-manager.smtp_servers.fields.rate_limit_per_hour'))
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText(__('filament-email-manager::filament-email-manager.smtp_servers.help_text.rate_limit'))
                            ->suffix(__('filament-email-manager::filament-email-manager.smtp_servers.suffixes.rate_limit')),
                    ]),

                LayoutComponents\Section::make(__('filament-email-manager::filament-email-manager.smtp_servers.sections.status'))
                    ->schema([
                        FormComponents\Toggle::make('is_active')
                            ->label(__('filament-email-manager::filament-email-manager.smtp_servers.fields.is_active'))
                            ->default(true)
                            ->helperText(__('filament-email-manager::filament-email-manager.smtp_servers.help_text.is_active')),

                        FormComponents\Toggle::make('is_default')
                            ->label(__('filament-email-manager::filament-email-manager.smtp_servers.fields.is_default'))
                            ->helperText(__('filament-email-manager::filament-email-manager.smtp_servers.help_text.is_default')),
                    ])
                    ->columns(2),

                LayoutComponents\Section::make(__('filament-email-manager::filament-email-manager.smtp_servers.sections.advanced_settings'))
                    ->schema([
                        FormComponents\KeyValue::make('settings')
                            ->label(__('filament-email-manager::filament-email-manager.smtp_servers.fields.settings'))
                            ->helperText(__('filament-email-manager::filament-email-manager.smtp_servers.help_text.settings'))
                            ->keyLabel(__('filament-email-manager::filament-email-manager.smtp_servers.labels.key'))
                            ->valueLabel(__('filament-email-manager::filament-email-manager.smtp_servers.labels.value')),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('host')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('port')
                    ->sortable(),

                Tables\Columns\TextColumn::make('encryption')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'tls' => 'success',
                        'ssl' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('from_email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('rate_limit_per_hour')
                    ->label('Rate Limit')
                    ->formatStateUsing(fn($state) => $state > 0 ? $state . '/hour' : 'Unlimited')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('emailLogs_count')
                    ->label('Emails Sent')
                    ->counts('emailLogs')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueLabel('Active servers')
                    ->falseLabel('Inactive servers')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->trueLabel('Default server')
                    ->falseLabel('Non-default servers')
                    ->native(false),
            ])
            ->actions([
                Actions\Action::make('test_connection')
                    ->icon('heroicon-o-bolt')
                    ->color('info')
                    ->action(function (SmtpServer $record) {
                        $mailConfig = app(MailConfigService::class);
                        $result = $mailConfig->testConnection($record);

                        if ($result['success']) {
                            Notification::make()
                                ->success()
                                ->title('Connection Successful!')
                                ->body("Successfully connected to {$record->host}:{$record->port}")
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Connection Failed')
                                ->body($result['error'])
                                ->send();
                        }
                    }),

                Actions\Action::make('send_test_email')
                    ->icon('heroicon-o-envelope')
                    ->color('warning')
                    ->form([
                        FormComponents\TextInput::make('test_email')
                            ->email()
                            ->required()
                            ->placeholder('test@example.com')
                            ->label('Test Email Address'),
                    ])
                    ->action(function (SmtpServer $record, array $data) {
                        $mailConfig = app(MailConfigService::class);
                        $result = $mailConfig->sendTestEmail(
                            $record,
                            $data['test_email'],
                            'Test Email from ' . $record->name
                        );

                        if ($result['success']) {
                            Notification::make()
                                ->success()
                                ->title('Test Email Sent!')
                                ->body("Test email sent to {$data['test_email']}")
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Failed to Send Test Email')
                                ->body($result['error'])
                                ->send();
                        }
                    }),

                Actions\Action::make('set_as_default')
                    ->icon('heroicon-o-star')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(SmtpServer $record) => !$record->is_default)
                    ->action(function (SmtpServer $record) {
                        $mailConfig = app(MailConfigService::class);
                        $mailConfig->setAsDefault($record);

                        Notification::make()
                            ->success()
                            ->title('Default Server Updated')
                            ->body("{$record->name} is now the default SMTP server")
                            ->send();
                    }),

                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmtpServers::route('/'),
            'create' => Pages\CreateSmtpServer::route('/create'),
            'edit' => Pages\EditSmtpServer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }
}
