<?php

namespace St693ava\FilamentEmailManager\Filament\Resources;

use Filament\Forms\Components as FormComponents;
use Filament\Schemas\Components as LayoutComponents;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use St693ava\FilamentEmailManager\Filament\Resources\EmailTemplateResource\Pages;
use St693ava\FilamentEmailManager\Models\EmailTemplate;
use St693ava\FilamentEmailManager\Models\EmailTemplateLayout;
use St693ava\FilamentEmailManager\Services\EmailService;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';


    public static function getNavigationLabel(): string
    {
        return __('filament-email-manager::filament-email-manager.email_templates.title');
    }

    public static function getModelLabel(): string
    {
        return __('filament-email-manager::filament-email-manager.email_templates.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-email-manager::filament-email-manager.email_templates.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                LayoutComponents\Section::make(__('filament-email-manager::filament-email-manager.email_templates.sections.template_information'))
                    ->schema([
                        FormComponents\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Welcome Email Template')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }),

                        FormComponents\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('welcome-email-template')
                            ->helperText('Used to identify this template programmatically'),

                        FormComponents\Select::make('layout_id')
                            ->label('Email Layout')
                            ->options(EmailTemplateLayout::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->helperText('Choose a layout to wrap your email content'),

                        FormComponents\Toggle::make('is_active')
                            ->default(true)
                            ->helperText('Enable/disable this template'),
                    ])
                    ->columns(2),

                LayoutComponents\Section::make(__('filament-email-manager::filament-email-manager.email_templates.sections.email_content'))
                    ->schema([
                        FormComponents\TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Welcome to {{company_name}}!')
                            ->helperText('Use {{placeholder}} format for dynamic content'),

                        FormComponents\RichEditor::make('content_html')
                            ->label('HTML Content')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'blockquote',
                                'codeBlock',
                            ])
                            ->placeholder('Use {{placeholder}} format for dynamic content')
                            ->helperText('Rich HTML content for your email'),

                        FormComponents\Textarea::make('content_text')
                            ->label('Text Content (Optional)')
                            ->rows(8)
                            ->placeholder('Plain text version of your email')
                            ->helperText('Plain text fallback for email clients that don\'t support HTML'),
                    ]),

                LayoutComponents\Section::make(__('filament-email-manager::filament-email-manager.email_templates.sections.placeholders_variables'))
                    ->schema([
                        FormComponents\Repeater::make('placeholders')
                            ->schema([
                                FormComponents\TextInput::make('name')
                                    ->required()
                                    ->placeholder('customer_name')
                                    ->helperText('Variable name (without braces)'),

                                FormComponents\TextInput::make('description')
                                    ->placeholder('Customer full name')
                                    ->helperText('Description for documentation'),

                                FormComponents\TextInput::make('default_value')
                                    ->placeholder('Dear Customer')
                                    ->helperText('Default value if not provided'),

                                FormComponents\Toggle::make('required')
                                    ->default(false)
                                    ->helperText('Is this placeholder required?'),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->addActionLabel('Add Placeholder')
                            ->helperText('Define placeholders that can be used in subject and content'),

                        FormComponents\TagsInput::make('merge_tags')
                            ->placeholder('customer_name, company_name, order_number')
                            ->helperText('Quick tags for common variables (will be available in rich editor)'),

                        FormComponents\KeyValue::make('default_values')
                            ->helperText('Default values for placeholders')
                            ->keyLabel('Placeholder')
                            ->valueLabel('Default Value'),
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

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('layout.name')
                    ->label('Layout')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('emailLogs_count')
                    ->label('Times Used')
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
                    ->trueLabel('Active templates')
                    ->falseLabel('Inactive templates')
                    ->native(false),

                Tables\Filters\SelectFilter::make('layout_id')
                    ->label('Layout')
                    ->options(EmailTemplateLayout::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->form([
                        LayoutComponents\Section::make(__('filament-email-manager::filament-email-manager.email_templates.sections.preview_data'))
                            ->schema(function (EmailTemplate $record) {
                                $fields = [];

                                foreach ($record->placeholders ?? [] as $placeholder) {
                                    $fields[] = FormComponents\TextInput::make("data.{$placeholder['name']}")
                                        ->label($placeholder['description'] ?? $placeholder['name'])
                                        ->default($placeholder['default_value'] ?? '')
                                        ->required($placeholder['required'] ?? false);
                                }

                                return $fields ?: [
                                    FormComponents\Placeholder::make('no_placeholders')
                                        ->content('This template has no placeholders defined.'),
                                ];
                            }),
                    ])
                    ->action(function (EmailTemplate $record, array $data) {
                        try {
                            $emailService = app(EmailService::class);
                            $preview = $emailService->preview($record, $data['data'] ?? []);

                            Notification::make()
                                ->success()
                                ->title('Preview Generated')
                                ->body('Email preview generated successfully')
                                ->send();

                            // You could redirect to a preview page or show in modal
                            // For now, we'll just show a success notification
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Preview Failed')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->modalWidth('4xl'),

                Actions\Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->action(function (EmailTemplate $record) {
                        $newTemplate = $record->replicate();
                        $newTemplate->name = $record->name . ' (Copy)';
                        $newTemplate->slug = $record->slug . '-copy';
                        $newTemplate->save();

                        return redirect(static::getUrl('edit', ['record' => $newTemplate]));
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
            'index' => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit' => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }
}