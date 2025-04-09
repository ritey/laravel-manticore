<?php

/**
 * Laravel Manticore Scout
 * (c) Ritey, MIT License.
 */

namespace Ritey\LaravelManticore;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Manticoresearch\Client;
use Ritey\LaravelManticore\Console\CreateManticoreIndex;
use Ritey\LaravelManticore\Console\SyncManticoreIndex;

class ManticoreServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel_manticore.php', 'laravel_manticore');

        $this->app->singleton(Client::class, function () {
            if (!class_exists(Client::class)) {
                throw new RuntimeException('Manticoresearch PHP client is not installed. Run composer require manticoresoftware/manticoresearch-php');
            }

            $host = config('laravel_manticore.host', '127.0.0.1');
            $port = config('laravel_manticore.port', 9308);
            $debug = config('laravel_manticore.debug', false);

            if ($debug) {
                Log::debug('[laravel-manticore] Connecting to Manticore using config:', [
                    'host' => $host,
                    'port' => $port,
                ]);
            }

            try {
                return new Client([
                    'host' => $host,
                    'port' => $port,
                ]);
            } catch (\Throwable $e) {
                throw new RuntimeException('Failed to connect to Manticore server: '.$e->getMessage());
            }
        });

        // Register engine early in lifecycle
        $this->app->afterResolving(EngineManager::class, function (EngineManager $manager) {
            $manager->extend('manticore', function () {
                return new ManticoreEngine(resolve(Client::class));
            });
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel_manticore.php' => config_path('laravel_manticore.php'),
        ], 'config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateManticoreIndex::class,
                SyncManticoreIndex::class,
            ]);
        }
    }
}
