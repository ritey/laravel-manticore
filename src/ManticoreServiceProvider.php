<?php

/**
 * Laravel Manticore Scout
 * (c) Ritey, MIT License.
 */

namespace Ritey\LaravelManticore;

use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use Manticoresearch\Client;
use Ritey\LaravelManticore\Console\CreateManticoreIndex;
use Ritey\LaravelManticore\Console\SyncManticoreIndex;

class ManticoreServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge package configuration with namespaced key
        $this->mergeConfigFrom(__DIR__.'/../config/manticore.php', 'laravel_manticore');

        // Bind Manticore Client singleton with graceful fallback
        $this->app->singleton(Client::class, function () {
            if (!class_exists('Manticoresearch\Client')) {
                throw new \RuntimeException('Manticoresearch PHP client is not installed. Run composer require manticoresoftware/manticoresearch-php');
            }

            try {
                return new Client([
                    'host' => config('laravel_manticore.host', '127.0.0.1'),
                    'port' => config('laravel_manticore.port', 9308),
                ]);
            } catch (\Throwable $e) {
                throw new \RuntimeException('Failed to connect to Manticore server: '.$e->getMessage());
            }
        });
    }

    public function boot()
    {
        // Publish configuration with renamed file under Laravel's default 'config' tag
        $this->publishes([
            __DIR__.'/../config/manticore.php' => config_path('laravel_manticore.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateManticoreIndex::class,
                SyncManticoreIndex::class,
            ]);
        }

        resolve(EngineManager::class)->extend('manticore', function () {
            return new ManticoreEngine(resolve(Client::class));
        });
    }
}
