<?php

/**
 * Laravel Manticore Scout
 * (c) Ritey, MIT License.
 */

namespace Ritey\LaravelManticore\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Manticoresearch\Client;

class SyncManticoreIndex extends Command
{
    protected $signature = 'manticore:sync-index {model}';
    protected $description = 'Sync a Manticore index with current Eloquent model data';

    public function handle()
    {
        $modelClass = $this->argument('model');

        if (!class_exists($modelClass)) {
            $this->error("Model class {$modelClass} not found.");

            return;
        }

        /** @var Model $model */
        $model = new $modelClass();

        if (!method_exists($model, 'toSearchableArray')) {
            $this->error('Model does not implement toSearchableArray().');

            return;
        }

        $indexName = $model->searchableAs();

        try {
            $client = app(Client::class);
            $documents = [];
            $total = $modelClass::count();

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            $modelClass::chunk(500, function ($models) use (&$documents, $client, $indexName, $bar) {
                foreach ($models as $model) {
                    $data = $model->toSearchableArray();
                    $data['id'] = $model->getScoutKey();
                    $documents[] = $data;

                    if (count($documents) >= 100) {
                        $client->index($indexName)->addDocuments($documents);
                        $documents = [];
                    }

                    $bar->advance();
                }

                if (!empty($documents)) {
                    $client->index($indexName)->addDocuments($documents);
                }
            });

            $bar->finish();
            $this->newLine();
            $this->info("Index {$indexName} synced successfully.");
        } catch (\Throwable $e) {
            $this->error('Manticore sync error: '.$e->getMessage());
        }
    }
}
