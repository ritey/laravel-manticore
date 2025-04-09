<?php

namespace Ritey\LaravelManticore\Console;

use Illuminate\Console\Command;
use Manticoresearch\Client;

class DeleteManticoreIndex extends Command
{
    protected $signature = 'manticore:delete-index {index}';
    protected $description = 'Delete a Manticore index by name';

    public function handle()
    {
        $index = $this->argument('index');

        try {
            $client = app(Client::class);
            $result = $client->sql(['body' => ['query' => 'SHOW TABLES']]);
            $indexes = collect($result['hits']['hits'] ?? [])->pluck('index')->toArray();

            if (!in_array($index, $indexes)) {
                $this->warn("Index '{$index}' does not exist.");

                return;
            }

            $client->indices()->drop(['index' => $index]);
            $this->info("Index '{$index}' deleted successfully.");
        } catch (\Throwable $e) {
            $this->error('Manticore delete error: '.$e->getMessage());
        }
    }
}
