<?php
/**
 * Laravel Manticore Scout
 * (c) Ritey, MIT License
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
        $this->app->singleton(Client::class, function () {
            return new Client([
                'host' => config('manticore.host', '127.0.0.1'),
                'port' => config('manticore.port', 9308),
            ]);
        });
    }

    public function boot()
    {
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
