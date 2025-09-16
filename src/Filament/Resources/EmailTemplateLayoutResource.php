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
                        Tab::make('Informação')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                FormComponents\TextInput::make('name')
                                    ->label('Nome')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Modern Email Layout'),

                                FormComponents\Toggle::make('is_default')
                                    ->label('Layout Pré-definido')
                                    ->helperText('Definir como layout pré-definido para novos modelos'),
                            ]),

                        Tab::make('Estrutura')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                FormComponents\CodeEditor::make('wrapper_html')
                                    ->language(Language::Html)
                                    ->label('Conteúdo HTML')
                                    ->required()
                                    ->helperText('Deve conter o marcador {{content}}. Marcadores opcionais: {{header}}, {{footer}}, {{css}}')
                                    ->default('<!DOCTYPE html><html><head><meta charset="utf-8"><style>{{css}}</style></head><body>{{header}}{{content}}{{footer}}</body></html>'),

                                FormComponents\CodeEditor::make('header_html')
                                    ->language(Language::Html)
                                    ->label('Conteúdo HTML do Cabeçalho')
                                    ->helperText('Conteúdo HTML para o cabeçalho do email'),

                                FormComponents\CodeEditor::make('footer_html')
                                    ->language(Language::Html)
                                    ->label('Conteúdo HTML do Rodapé')
                                    ->helperText('Conteúdo HTML para o rodapé do email'),
                            ]),

                        Tab::make('Tema')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                FormComponents\CodeEditor::make('css_styles')
                                    ->language(Language::Css)
                                    ->label('Estilos CSS')
                                    ->helperText('Estilos CSS para o modelo de email'),
                            ]),

                        Tab::make('Configurações')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                FormComponents\KeyValue::make('settings')
                                    ->label('Configurações')
                                    ->helperText('Opções de configuração de layout adicionais')
                                    ->keyLabel('Configuração')
                                    ->valueLabel('Valor'),
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
                    ->label('Nome')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->label('Layout Pré-definido')
                    ->sortable(),

                Tables\Columns\TextColumn::make('emailTemplates_count')
                    ->label('Modelos a Usar')
                    ->counts('emailTemplates')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Criado em')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Atualizado em')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Layout Pré-definido')
                    ->boolean()
                    ->trueLabel('Layout pré-definido')
                    ->falseLabel('Layouts não pré-definidos')
                    ->native(false),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\Action::make('preview')
                        ->label('Pré-visualizar')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalHeading('Pré-visualizar')
                        ->modalContent(function (EmailTemplateLayout $record) {
                            $previewUrl = route('filament-email-manager.preview.layout', $record);

                            return view('filament-email-manager::preview', [
                                'previewUrl' => $previewUrl,
                            ]);
                        })
                        ->modalWidth('7xl')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Fechar')
                        ->extraModalFooterActions(function (EmailTemplateLayout $record) {
                            return [
                                Actions\Action::make('openInNewTab')
                                    ->label('Abrir em nova aba')
                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                    ->color('gray')
                                    ->url(route('filament-email-manager.preview.layout', $record))
                                    ->openUrlInNewTab(),
                            ];
                        }),

                    Actions\Action::make('duplicate')
                        ->label('Duplicar')
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
                ]),
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
