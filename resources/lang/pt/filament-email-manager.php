<?php

return [
    'navigation' => [
        'group' => 'Gestão de Email',
        'smtp_servers' => 'Servidores SMTP',
        'email_templates' => 'Modelos de Email',
        'email_template_layouts' => 'Layouts de Email',
        'email_logs' => 'Histórico de Emails',
    ],

    'smtp_servers' => [
        'title' => 'Servidores SMTP',
        'singular' => 'Servidor SMTP',
        'plural' => 'Servidores SMTP',
        'navigation_badge' => '{count} ativos',

        'fields' => [
            'name' => 'Nome',
            'host' => 'Servidor',
            'port' => 'Porta',
            'encryption' => 'Encriptação',
            'username' => 'Nome de utilizador',
            'password' => 'Palavra-passe',
            'from_email' => 'Email do remetente',
            'from_name' => 'Nome do remetente',
            'rate_limit_per_hour' => 'Limite por hora',
            'is_active' => 'Ativo',
            'is_default' => 'Predefinido',
            'settings' => 'Configurações avançadas',
            'created_at' => 'Criado em',
            'emails_sent' => 'Emails enviados',
        ],

        'sections' => [
            'server_configuration' => 'Configuração do Servidor',
            'sender_configuration' => 'Configuração do Remetente',
            'rate_limiting' => 'Limitação de Taxa',
            'status' => 'Estado',
            'advanced_settings' => 'Configurações Avançadas',
        ],

        'placeholders' => [
            'name' => 'Servidor SMTP Gmail',
            'host' => 'smtp.gmail.com',
            'username' => 'o-seu-email@dominio.com',
            'password' => 'A sua palavra-passe SMTP',
            'from_email' => 'noreply@seudominio.com',
            'from_name' => 'Nome da Sua App',
        ],

        'help_text' => [
            'encryption' => 'TLS é recomendado para a maioria dos servidores SMTP. A porta será ajustada automaticamente.',
            'rate_limit' => 'Máximo de emails por hora (0 = ilimitado)',
            'is_active' => 'Ativar/desativar este servidor SMTP',
            'is_default' => 'Definir como servidor SMTP predefinido para novos emails',
            'settings' => 'Opções de configuração SMTP adicionais (opcional)',
        ],

        'actions' => [
            'test_connection' => 'Testar Ligação',
            'send_test_email' => 'Enviar Email de Teste',
            'set_as_default' => 'Definir como Predefinido',
            'test_email_address' => 'Endereço de Email de Teste',
        ],

        'messages' => [
            'connection_successful' => 'Ligação Bem-sucedida!',
            'connection_successful_body' => 'Ligação estabelecida com sucesso a {host}:{port}',
            'connection_failed' => 'Falha na Ligação',
            'test_email_sent' => 'Email de Teste Enviado!',
            'test_email_sent_body' => 'Email de teste enviado para {email}',
            'test_email_failed' => 'Falha ao Enviar Email de Teste',
            'default_server_updated' => 'Servidor Predefinido Atualizado',
            'default_server_updated_body' => '{name} é agora o servidor SMTP predefinido',
        ],

        'filters' => [
            'active' => 'Ativo',
            'active_servers' => 'Servidores ativos',
            'inactive_servers' => 'Servidores inativos',
            'default' => 'Predefinido',
            'default_server' => 'Servidor predefinido',
            'non_default_servers' => 'Servidores não predefinidos',
        ],

        'options' => [
            'encryption_none' => 'Nenhuma',
            'encryption_tls' => 'TLS',
            'encryption_ssl' => 'SSL',
        ],

        'suffixes' => [
            'rate_limit' => 'emails/hora',
        ],
    ],

    'email_templates' => [
        'title' => 'Modelos de Email',
        'singular' => 'Modelo de Email',
        'plural' => 'Modelos de Email',
        'navigation_badge' => '{count} ativos',

        'fields' => [
            'name' => 'Nome',
            'slug' => 'Identificador',
            'layout_id' => 'Layout de Email',
            'is_active' => 'Ativo',
            'subject' => 'Assunto',
            'content_html' => 'Conteúdo HTML',
            'content_text' => 'Conteúdo de Texto (Opcional)',
            'placeholders' => 'Marcadores de Posição',
            'merge_tags' => 'Etiquetas de Junção',
            'default_values' => 'Valores Predefinidos',
            'created_at' => 'Criado em',
            'times_used' => 'Vezes Utilizado',
        ],

        'sections' => [
            'template_information' => 'Informação do Modelo',
            'email_content' => 'Conteúdo do Email',
            'placeholders_variables' => 'Marcadores de Posição e Variáveis',
        ],

        'placeholders' => [
            'name' => 'Modelo de Email de Boas-vindas',
            'slug' => 'modelo-email-boas-vindas',
            'subject' => 'Bem-vindo à {{nome_empresa}}!',
            'content_text' => 'Versão em texto simples do seu email',
        ],

        'help_text' => [
            'slug' => 'Usado para identificar este modelo programaticamente',
            'layout' => 'Escolha um layout para envolver o conteúdo do seu email',
            'is_active' => 'Ativar/desativar este modelo',
            'subject' => 'Use o formato {{marcador}} para conteúdo dinâmico',
            'content_html' => 'Conteúdo HTML rico para o seu email',
            'content_text' => 'Alternativa em texto simples para clientes de email que não suportam HTML',
            'placeholders' => 'Defina marcadores que podem ser usados no assunto e conteúdo',
            'merge_tags' => 'Etiquetas rápidas para variáveis comuns (estarão disponíveis no editor rico)',
            'default_values' => 'Valores predefinidos para marcadores',
        ],

        'placeholder_fields' => [
            'name' => 'Nome',
            'description' => 'Descrição',
            'default_value' => 'Valor predefinido',
            'required' => 'Obrigatório',
            'name_placeholder' => 'nome_cliente',
            'description_placeholder' => 'Nome completo do cliente',
            'default_value_placeholder' => 'Caro Cliente',
            'required_help' => 'É obrigatório este marcador?',
        ],

        'key_value' => [
            'placeholder_label' => 'Marcador',
            'default_value_label' => 'Valor Predefinido',
        ],

        'actions' => [
            'preview' => 'Pré-visualizar',
            'duplicate' => 'Duplicar',
            'add_placeholder' => 'Adicionar Marcador',
        ],

        'preview' => [
            'title' => 'Pré-visualização Gerada',
            'failed' => 'Falha na Pré-visualização',
            'generated_successfully' => 'Pré-visualização do email gerada com sucesso',
            'preview_data' => 'Dados de Pré-visualização',
            'no_placeholders' => 'Este modelo não tem marcadores definidos.',
        ],

        'duplicate' => [
            'name_suffix' => ' (Cópia)',
            'slug_suffix' => '-copia',
        ],

        'filters' => [
            'active' => 'Ativo',
            'active_templates' => 'Modelos ativos',
            'inactive_templates' => 'Modelos inativos',
            'layout' => 'Layout',
        ],
    ],

    'email_template_layouts' => [
        'title' => 'Layouts de Email',
        'singular' => 'Layout de Email',
        'plural' => 'Layouts de Email',
        'navigation_badge' => '{count} layouts',

        'fields' => [
            'name' => 'Nome',
            'is_default' => 'Predefinido',
            'wrapper_html' => 'HTML Envolvente',
            'header_html' => 'HTML do Cabeçalho',
            'footer_html' => 'HTML do Rodapé',
            'css_styles' => 'Estilos CSS',
            'settings' => 'Configurações',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
            'templates_using' => 'Modelos a Usar',
        ],

        'sections' => [
            'layout_information' => 'Informação do Layout',
            'layout_structure' => 'Estrutura do Layout',
            'styling' => 'Estilização',
            'advanced_settings' => 'Configurações Avançadas',
        ],

        'placeholders' => [
            'name' => 'Layout de Email Moderno',
        ],

        'help_text' => [
            'is_default' => 'Definir como layout predefinido para novos modelos',
            'wrapper_html' => 'Deve conter o marcador {{content}}. Marcadores opcionais: {{header}}, {{footer}}, {{css}}',
            'header_html' => 'Conteúdo HTML para o cabeçalho do email',
            'footer_html' => 'Conteúdo HTML para o rodapé do email',
            'css_styles' => 'Estilos CSS para o modelo de email',
            'settings' => 'Opções de configuração de layout adicionais',
        ],

        'defaults' => [
            'wrapper_html' => '<!DOCTYPE html><html><head><meta charset="utf-8"><style>{{css}}</style></head><body>{{header}}{{content}}{{footer}}</body></html>',
        ],

        'actions' => [
            'preview' => 'Pré-visualizar',
            'duplicate' => 'Duplicar',
        ],

        'preview' => [
            'title' => 'Pré-visualização do Layout',
            'sample_content' => '<h2>Conteúdo de Email de Exemplo</h2><p>Assim é como o seu layout de email ficará com conteúdo.</p>',
        ],

        'duplicate' => [
            'name_suffix' => ' (Cópia)',
        ],

        'filters' => [
            'default_layout' => 'Layout Predefinido',
            'default_layout_true' => 'Layout predefinido',
            'non_default_layouts' => 'Layouts não predefinidos',
        ],
    ],

    'email_logs' => [
        'title' => 'Histórico de Emails',
        'singular' => 'Registo de Email',
        'plural' => 'Registos de Email',
        'navigation_badge' => '{count} enviados',

        'fields' => [
            'subject' => 'Assunto',
            'to_recipients' => 'Para',
            'smtp_server' => 'Servidor SMTP',
            'status' => 'Estado',
            'sent_at' => 'Enviado em',
            'created_at' => 'Criado em',
        ],

        'statuses' => [
            'sent' => 'Enviado',
            'pending' => 'Pendente',
            'failed' => 'Falhado',
        ],

        'actions' => [
            'view' => 'Ver',
        ],

        'filters' => [
            'status' => 'Estado',
            'smtp_server' => 'Servidor SMTP',
        ],
    ],

    'common' => [
        'actions' => [
            'create' => 'Criar',
            'edit' => 'Editar',
            'delete' => 'Eliminar',
            'view' => 'Ver',
            'save' => 'Guardar',
            'cancel' => 'Cancelar',
            'close' => 'Fechar',
            'submit' => 'Submeter',
            'confirm' => 'Confirmar',
        ],

        'labels' => [
            'unlimited' => 'Ilimitado',
            'required' => 'Obrigatório',
            'optional' => 'Opcional',
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'yes' => 'Sim',
            'no' => 'Não',
        ],

        'messages' => [
            'saved_successfully' => 'Guardado com sucesso',
            'deleted_successfully' => 'Eliminado com sucesso',
            'created_successfully' => 'Criado com sucesso',
            'updated_successfully' => 'Atualizado com sucesso',
        ],
    ],
];