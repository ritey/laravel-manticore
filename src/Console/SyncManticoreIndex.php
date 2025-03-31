<?php

namespace Ritey\LaravelManticore\Console;

use Illuminate\Console\Command;
use Manticoresearch\Client;

class SyncManticoreIndex extends Command
{
    protected $signature = 'manticore:sync-index {model}';
    protected $description = 'Sync Manticore RT index with Laravel model schema';

    public function handle()
    {
        $modelClass = $this->argument('model');
        if (!class_exists($modelClass)) {
            $this->error("Model {$modelClass} does not exist.");
            return;
        }

        $model = new $modelClass;
        if (!method_exists($model, 'toSearchableArray')) {
            $this->error("Model does not implement toSearchableArray().");
            return;
        }

        $index = $model->searchableAs();
        $fields = $model->toSearchableArray();
        if (!is_array($fields) || empty($fields)) {
            $this->error("toSearchableArray() returned no fields.");
            return;
        }

        try {
            $client = app(Client::class);
            $existing = $client->sql("DESCRIBE {$index}");
            $existingColumns = array_column($existing, 'Field');

            foreach ($fields as $key => $value) {
                if (in_array($key, $existingColumns)) {
                    continue;
                }

                if (is_array($value) && isset($value[0]) && is_float($value[0])) {
                    $sql = "ALTER TABLE {$index} ADD COLUMN {$key} VECTOR(" . count($value) . ") TYPE FLOAT";
                } elseif (is_numeric($value)) {
                    $sql = "ALTER TABLE {$index} ADD COLUMN {$key} FLOAT";
                } elseif (is_string($value)) {
                    $sql = "ALTER TABLE {$index} ADD COLUMN {$key} TEXT";
                } else {
                    $sql = "ALTER TABLE {$index} ADD COLUMN {$key} JSON";
                }

                $this->info("Executing: {$sql}");
                $client->sql($sql);
            }

            $this->info("Index {$index} synced successfully.");
        } catch (\Throwable $e) {
            $this->error("Failed to sync: " . $e->getMessage());
        }
    }
}
