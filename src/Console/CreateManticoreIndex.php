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
    protected $signature = 'manticore:create-index {model} {--fields=}';
    protected $description = 'Create a Manticore RT index based on a Laravel searchable model, with optional fallback fields if no records exist';

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

        $fields = [];

        // Priority: --fields override > database record > model stub
        if ($this->option('fields')) {
            $fields = collect(explode(',', $this->option('fields')))
                ->mapWithKeys(function ($field) {
                    $parts = explode(':', $field);

                    return [$parts[0] => $parts[1] ?? 'text'];
                })->all()
            ;
        } else {
            $fields = $model->toSearchableArray();
        }

        if (empty(array_filter($fields))) {
            $this->error('No fields found from model or --fields option.');

            return;
        }

        $indexName = $model->searchableAs();
        $schema = ['id' => ['type' => 'bigint']];

        foreach ($fields as $key => $value) {
            // Skip null values (but log them if needed)
            if (is_null($value)) {
                $schema[$key] = ['type' => 'text']; // default fallback

                continue;
            }

            // If user passed type explicitly (e.g. from --fields)
            if (is_string($value) && in_array($value, ['text', 'float', 'int', 'integer', 'json', 'string', 'float[]'])) {
                $schema[$key] = ['type' => $value];

                continue;
            }

            // Vector detection
            if (is_array($value) && isset($value[0]) && is_float($value[0])) {
                $schema[$key] = ['type' => 'float[]'];
            }
            // Associative array: likely JSON
            elseif (is_array($value) && array_keys($value) !== range(0, count($value) - 1)) {
                $schema[$key] = ['type' => 'json', 'options' => ['json_secondary_indexes' => '1']];
            } elseif (is_int($value)) {
                $schema[$key] = ['type' => 'int'];
            } elseif (is_float($value)) {
                $schema[$key] = ['type' => 'float'];
            } elseif (is_string($value)) {
                $schema[$key] = ['type' => 'text'];
            } else {
                $schema[$key] = ['type' => 'json'];
            }
        }

        $settings = config('laravel_manticore.defaults.index_settings', []);

        try {
            $table = app(Client::class)->table($indexName);
            $table->create($schema, $settings);

            $this->info("Index {$indexName} created successfully.");
        } catch (\Throwable $e) {
            $this->error('Manticore create error: '.$e->getMessage());
        }
    }
}
