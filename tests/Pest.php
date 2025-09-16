<?php

use St693ava\FilamentEmailManager\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

// Helpers globais para os testes
function createSmtpServer(array $attributes = []): \St693ava\FilamentEmailManager\Models\SmtpServer
{
    return \St693ava\FilamentEmailManager\Models\SmtpServer::factory()->create($attributes);
}

function createEmailTemplate(array $attributes = []): \St693ava\FilamentEmailManager\Models\EmailTemplate
{
    return \St693ava\FilamentEmailManager\Models\EmailTemplate::factory()->create($attributes);
}

function createEmailTemplateLayout(array $attributes = []): \St693ava\FilamentEmailManager\Models\EmailTemplateLayout
{
    return \St693ava\FilamentEmailManager\Models\EmailTemplateLayout::factory()->create($attributes);
}

function createEmailLog(array $attributes = []): \St693ava\FilamentEmailManager\Models\EmailLog
{
    return \St693ava\FilamentEmailManager\Models\EmailLog::factory()->create($attributes);
}