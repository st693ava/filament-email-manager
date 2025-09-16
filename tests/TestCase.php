<?php

namespace St693ava\FilamentEmailManager\Tests;

use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use St693ava\FilamentEmailManager\FilamentEmailManagerServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Desabilitar Vite durante os testes
        $this->withoutVite();

        // Executar migrações fresh para cada teste
        $this->artisan('migrate:fresh');
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            SupportServiceProvider::class,
            FormsServiceProvider::class,
            TablesServiceProvider::class,
            FilamentServiceProvider::class,
            FilamentEmailManagerServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        // Carregar migrações do package
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getEnvironmentSetUp($app)
    {
        // Configurar ambiente de teste
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('mail.default', 'log');
        $app['config']->set('queue.default', 'sync');
    }
}