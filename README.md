# Filament Email Manager

Um package completo para gestão de emails no Filament v4 com suporte para múltiplos servidores SMTP, templates de email e histórico de envios.

## Funcionalidades

### 📧 Gestão de Servidores SMTP
- Configuração de múltiplos servidores SMTP
- Atualização automática de porta baseada na encriptação
- Teste de ligação SMTP
- Envio de emails de teste
- Rate limiting por servidor (emails por hora)
- Servidor SMTP padrão configurável

### 🎨 Templates de Email
- Criação e gestão de templates de email
- Editor rico para conteúdo HTML
- Suporte para placeholders dinâmicos ({{variável}})
- Versão texto alternativa
- Sistema de preview com dados de teste
- Duplicação de templates

### 🖼️ Layouts de Email
- Layouts reutilizáveis para templates
- Editor de código para HTML/CSS
- Suporte para cabeçalho e rodapé personalizados
- Estilos CSS integrados
- Preview de layouts

### 📊 Histórico de Emails
- Log completo de emails enviados
- Estado de envio (enviado, pendente, falhado)
- Múltiplos destinatários (to, cc, bcc) em JSON
- Anexos múltiplos em JSON
- Download de ficheiros .eml
- Filtros por estado e servidor SMTP

## Requisitos

- Laravel 12+
- Filament v4
- PHP 8.3+

## Instalação

1. Instale o package via Composer:
```bash
composer require st693ava/filament-email-manager
```

2. Publique e execute as migrações:
```bash
php artisan vendor:publish --provider="St693ava\FilamentEmailManager\FilamentEmailManagerServiceProvider" --tag="migrations"
php artisan migrate
```

3. (Opcional) Publique o ficheiro de configuração:
```bash
php artisan vendor:publish --provider="St693ava\FilamentEmailManager\FilamentEmailManagerServiceProvider" --tag="config"
```

## Configuração

### Adicionando ao Painel Filament

No seu `AdminPanelProvider`, adicione o discovery dos recursos:

```php
public function panel(Panel $panel): Panel
{
    return $panel
        // ... outras configurações
        ->discoverResources(
            in: base_path('vendor/st693ava/filament-email-manager/src/Filament/Resources'),
            for: 'St693ava\FilamentEmailManager\Filament\Resources'
        );
}
```

### Configuração de Rate Limiting

O package inclui rate limiting automático baseado nas configurações de cada servidor SMTP:

- `0` = ilimitado
- `> 0` = número máximo de emails por hora

### Uso Programático

#### Enviando Emails

```php
use St693ava\FilamentEmailManager\Services\EmailService;

$emailService = app(EmailService::class);

// Enviar email usando template
$emailService->sendUsingTemplate(
    templateSlug: 'welcome-email',
    recipients: ['user@example.com'],
    data: ['nome_cliente' => 'João Silva'],
    smtpServerId: 1
);
```

#### Gerando Arquivos .eml

```php
use St693ava\FilamentEmailManager\Services\EmlGeneratorService;

$emlService = app(EmlGeneratorService::class);
$emlPath = $emlService->generateEml($emailLog);
```

## Traduções

O package inclui traduções completas para **Português de Portugal**. Para usar outros idiomas, publique os ficheiros de tradução:

```bash
php artisan vendor:publish --provider="St693ava\FilamentEmailManager\FilamentEmailManagerServiceProvider" --tag="lang"
```

## Personalização

### Configuração Personalizada

Publique o ficheiro de configuração para personalizar:

```bash
php artisan vendor:publish --provider="St693ava\FilamentEmailManager\FilamentEmailManagerServiceProvider" --tag="config"
```

### Views Personalizadas

Para personalizar as views:

```bash
php artisan vendor:publish --provider="St693ava\FilamentEmailManager\FilamentEmailManagerServiceProvider" --tag="views"
```

## Estrutura da Base de Dados

### Tabelas Principais

- `smtp_servers` - Configurações de servidores SMTP
- `email_template_layouts` - Layouts reutilizáveis
- `email_templates` - Templates de email
- `email_logs` - Histórico de emails enviados
- `email_queue` - Fila de emails agendados

### Campos JSON

O package utiliza campos JSON para flexibilidade:

- `recipients` - Múltiplos destinatários (to, cc, bcc)
- `attachments` - Múltiplos anexos
- `placeholders` - Variáveis dinâmicas nos templates

## Testes

Execute os testes do package:

```bash
vendor/bin/pest
```

## Contribuições

Contribuições são bem-vindas! Por favor:

1. Fork o repositório
2. Crie uma branch para a funcionalidade
3. Faça commit das alterações
4. Envie um Pull Request

## Licença

Este package é open-source sob a [MIT License](LICENSE).

## Suporte

Para questões e suporte:

- Issues: [GitHub Issues](https://github.com/st693ava/filament-email-manager/issues)
- Documentação: [Wiki](https://github.com/st693ava/filament-email-manager/wiki)

## Changelog

### v1.0.0
- Lançamento inicial
- Gestão completa de servidores SMTP
- Sistema de templates e layouts
- Histórico de emails
- Suporte para Filament v4
- Traduções em Português