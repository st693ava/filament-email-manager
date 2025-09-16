<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Email Manager Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the Filament Email Manager package.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Default rate limiting configuration for SMTP servers when no specific
    | limit is set. Set to 0 for unlimited.
    |
    */
    'default_rate_limit' => 100,

    /*
    |--------------------------------------------------------------------------
    | EML Storage
    |--------------------------------------------------------------------------
    |
    | Configuration for storing .eml files. These files are generated
    | for each sent email and can be downloaded later.
    |
    */
    'eml_storage' => [
        'disk' => 'local',
        'directory' => 'emails/eml',
        'cleanup_after_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Templates
    |--------------------------------------------------------------------------
    |
    | Configuration for email templates and layouts.
    |
    */
    'templates' => [
        'default_layout_wrapper' => '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>{{content}}</body></html>',
        'merge_tag_pattern' => '/\{\{(\w+)\}\}/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for queuing email jobs.
    |
    */
    'queue' => [
        'connection' => 'default',
        'queue' => 'emails',
        'delay_between_emails' => 2, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Filament navigation.
    |
    */
    'navigation' => [
        'group' => 'Email Management',
        'sort' => 100,
    ],
];