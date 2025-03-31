<?php

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

        $model = new $modelClass;
        if (!method_exists($model, 'toSearchableArray')) {
            $this->error("Model does not implement toSearchableArray().");
            return;
        }

        $fields = $model->toSearchableArray();
        if (!is_array($fields) || empty($fields)) {
            $this->error("toSearchableArray() returned empty or invalid data.");
            return;
        }

        $indexName = $model->searchableAs();
        $columns = ['id BIGINT'];

        foreach ($fields as $key => $value) {
            if (is_array($value) && isset($value[0]) && is_float($value[0])) {
                $columns[] = "{$key} VECTOR(" . count($value) . ") TYPE FLOAT";
            } elseif (is_numeric($value)) {
                $columns[] = "{$key} FLOAT";
            } elseif (is_string($value)) {
                $columns[] = "{$key} TEXT";
            } else {
                $columns[] = "{$key} JSON";
            }
        }

        $sql = "CREATE TABLE IF NOT EXISTS {$indexName} (" . implode(', ', $columns) . ")";
        try {
            app(Client::class)->sql($sql);
            $this->info("Index {$indexName} created successfully.");
        } catch (\Throwable $e) {
            $this->error("SQL Error: " . $e->getMessage());
        }
    }
}
