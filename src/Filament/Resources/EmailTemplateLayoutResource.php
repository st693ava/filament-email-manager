<?php

namespace St693ava\FilamentEmailManager\Filament\Resources;

use Filament\Forms\Components as FormComponents;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use St693ava\FilamentEmailManager\Filament\Resources\EmailTemplateLayoutResource\Pages;
use St693ava\FilamentEmailManager\Models\EmailTemplateLayout;

class EmailTemplateLayoutResource extends Resource
{
    protected static ?string $model = EmailTemplateLayout::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';


    public static function getNavigationLabel(): string
    {
        return __('filament-email-manager::filament-email-manager.email_template_layouts.title');
    }

    public static function getModelLabel(): string
    {
        return __('filament-email-manager::filament-email-manager.email_template_layouts.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-email-manager::filament-email-manager.email_template_layouts.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('EmailLayoutTabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Information')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                FormComponents\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Modern Email Layout'),

                                FormComponents\Toggle::make('is_default')
                                    ->helperText('Set as default layout for new templates'),
                            ]),

                        Tab::make('Structure')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                FormComponents\CodeEditor::make('wrapper_html')
                                    ->language(Language::Html)
                                    ->required()
                                    ->helperText('Must contain {{content}} placeholder. Optional placeholders: {{header}}, {{footer}}, {{css}}')
                                    ->default('<!DOCTYPE html><html><head><meta charset="utf-8"><style>{{css}}</style></head><body>{{header}}{{content}}{{footer}}</body></html>'),

                                FormComponents\CodeEditor::make('header_html')
                                    ->language(Language::Html)
                                    ->helperText('HTML content for the email header'),

                                FormComponents\CodeEditor::make('footer_html')
                                    ->language(Language::Html)
                                    ->helperText('HTML content for the email footer'),
                            ]),

                        Tab::make('Styling')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                FormComponents\CodeEditor::make('css_styles')
                                    ->language(Language::Css)
                                    ->helperText('CSS styles for the email template'),
                            ]),

                        Tab::make('Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                FormComponents\KeyValue::make('settings')
                                    ->helperText('Additional layout configuration options')
                                    ->keyLabel('Setting')
                                    ->valueLabel('Value'),
                            ]),
                    ])

                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('emailTemplates_count')
                    ->label('Templates Using')
                    ->counts('emailTemplates')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default Layout')
                    ->boolean()
                    ->trueLabel('Default layout')
                    ->falseLabel('Non-default layouts')
                    ->native(false),
            ])
            ->actions([
                Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Layout Preview')
                    ->modalContent(function (EmailTemplateLayout $record) {
                        $previewUrl = route('filament-email-manager.preview.layout', $record);

                        return view('filament-email-manager::preview', [
                            'previewUrl' => $previewUrl,
                        ]);
                    })
                    ->modalWidth('7xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->extraModalFooterActions(function (EmailTemplateLayout $record) {
                        return [
                            Actions\Action::make('openInNewTab')
                                ->label('Open in new tab')
                                ->icon('heroicon-o-arrow-top-right-on-square')
                                ->color('gray')
                                ->url(route('filament-email-manager.preview.layout', $record))
                                ->openUrlInNewTab(),
                        ];
                    }),

                Actions\Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->action(function (EmailTemplateLayout $record) {
                        $newLayout = $record->replicate(['email_templates_count']);
                        $newLayout->name = $record->name . ' (Copy)';
                        $newLayout->is_default = false;
                        $newLayout->save();

                        return redirect(static::getUrl('edit', ['record' => $newLayout]));
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
            'index' => Pages\ListEmailTemplateLayouts::route('/'),
            'create' => Pages\CreateEmailTemplateLayout::route('/create'),
            'edit' => Pages\EditEmailTemplateLayout::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
