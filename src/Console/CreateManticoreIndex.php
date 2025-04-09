<?php

/**
 * Laravel Manticore Scout
 * (c) Ritey, MIT License.
 */

namespace Ritey\LaravelManticore\Console;

use Illuminate\Console\Command;
use Manticoresearch\Client;

class CreateManticoreIndex extends Command
{
    protected $signature = 'manticore:create-index {model}';
    protected $description = 'Create a Manticore RT index based on a Laravel searchable model';

    public function handle()
    {
        $modelClass = $this->argument('model');

        if (!class_exists($modelClass)) {
            $this->error("Model class {$modelClass} not found.");

            return;
        }

        $model = $modelClass::query()->first() ?? new $modelClass();

        if (!method_exists($model, 'toSearchableArray')) {
            $this->error('Model does not implement toSearchableArray().');

            return;
        }

        $fields = $model->toSearchableArray();

        // If fields are empty and fallback provided, parse --fields option
        if (empty(array_filter($fields)) && $this->option('fields')) {
            $fields = collect(explode(',', $this->option('fields')))
                ->mapWithKeys(function ($field) {
                    $parts = explode(':', $field);

                    return [$parts[0] => $parts[1] ?? 'text'];
                })->all()
            ;
        }

        if (empty($fields)) {
            $this->error('No fields found from model or --fields option.');

            return;
        }

        $indexName = $model->searchableAs();
        $schema = [['id' => 'bigint']];

        foreach ($fields as $key => $value) {
            if (is_array($value) && isset($value[0]) && is_float($value[0])) {
                $schema[] = [$key => 'float[]'];
            } elseif (is_numeric($value)) {
                $schema[] = [$key => 'float'];
            } elseif (is_string($value)) {
                $schema[] = [$key => 'text'];
            } elseif (in_array($value, ['text', 'float', 'json', 'string', 'int', 'float[]'])) {
                $schema[] = [$key => $value];
            } else {
                $schema[] = [$key => 'json'];
            }
        }

        $settings = config('laravel_manticore.defaults.index_settings', []);

        try {
            app(Client::class)->indices()->create([
                'index' => $indexName,
                'body' => [
                    'settings' => $settings,
                    'schema' => $schema,
                ],
            ]);
            $this->info("Index {$indexName} created successfully.");
        } catch (\Throwable $e) {
            $this->error('Manticore create error: '.$e->getMessage());
        }
    }
}
