<?php

namespace Ritey\LaravelManticore\Console;

use Illuminate\Console\Command;
use Manticoresearch\Client;

class ListManticoreIndexes extends Command
{
    protected $signature = 'manticore:list-indexes';
    protected $description = 'List all Manticore indexes available';

    public function handle()
    {
        try {
            $client = app(Client::class);
            $result = $client->sql(['body' => ['query' => 'SHOW TABLES']]);
            $indexes = collect($result['hits']['hits'] ?? []);

            if ($indexes->isEmpty()) {
                $this->info('No indexes found.');

                return;
            }

            foreach ($indexes as $entry) {
                $this->line('- '.($entry['index'] ?? $entry['name'] ?? '[unknown]'));
            }
        } catch (Throwable $e) {
            $this->error('Manticore list error: '.$e->getMessage());
        }
    }
}
