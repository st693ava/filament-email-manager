<?php

namespace St693ava\FilamentEmailManager;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use St693ava\FilamentEmailManager\Models\SmtpServer;

class FilamentEmailManagerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-email-manager')
            ->hasConfigFile()
            ->hasMigrations([
                'create_smtp_servers_table',
                'create_email_template_layouts_table',
                'create_email_templates_table',
                'create_email_logs_table',
                'create_email_queue_table',
            ])
            ->hasViews()
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        // Register rate limiters
        $this->registerRateLimiters();

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }

        // Register Filament assets
        $this->registerFilamentAssets();

        // Register routes
        $this->registerRoutes();
    }

    public function packageRegistered(): void
    {
        // Register services
        $this->app->singleton(
            \St693ava\FilamentEmailManager\Services\MailConfigService::class
        );

        $this->app->singleton(
            \St693ava\FilamentEmailManager\Services\EmailService::class
        );

        $this->app->singleton(
            \St693ava\FilamentEmailManager\Services\EmlGeneratorService::class
        );
    }

    protected function registerRateLimiters(): void
    {
        // Rate limiter for SMTP servers
        RateLimiter::for('smtp-server', function (Request $request, $serverId) {
            $server = SmtpServer::find($serverId);

            if (!$server || $server->rate_limit_per_hour <= 0) {
                return Limit::none();
            }

            return Limit::perHour($server->rate_limit_per_hour)
                ->by('smtp-server-' . $serverId)
                ->response(function (Request $request, array $headers) use ($server) {
                    return response()->json([
                        'message' => "Rate limit exceeded for SMTP server '{$server->name}'. Limit: {$server->rate_limit_per_hour} emails per hour.",
                        'retry_after' => $headers['Retry-After'] ?? 3600,
                    ], 429, $headers);
                });
        });

        // Global email rate limiter (fallback)
        RateLimiter::for('emails', function (Request $request) {
            $defaultLimit = config('filament-email-manager.default_rate_limit', 100);

            return Limit::perHour($defaultLimit)
                ->by($request->user()?->id ?: $request->ip());
        });
    }

    protected function registerCommands(): void
    {
        $this->commands([
            \St693ava\FilamentEmailManager\Console\Commands\InstallDefaultLayoutCommand::class,
        ]);
    }

    protected function registerFilamentAssets(): void
    {
        // Register JavaScript assets for rich editor customizations
        // TODO: Add when we have the JavaScript files
        // FilamentAsset::register([
        //     Js::make('filament-email-manager-rich-editor', __DIR__ . '/../resources/js/rich-editor.js'),
        // ], 'st693ava/filament-email-manager');
    }

    protected function registerRoutes(): void
    {
        Route::middleware(['web'])
            ->prefix('filament-email-manager')
            ->name('filament-email-manager.')
            ->group(function () {
                Route::get('/preview/layout/{layout}', [\St693ava\FilamentEmailManager\Http\Controllers\EmailPreviewController::class, 'previewLayout'])
                    ->name('preview.layout');
            });
    }
}