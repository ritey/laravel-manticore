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
            $response = $client->indices()->show();
            if (empty($response)) {
                $this->info('No indexes found.');

                return;
            }

            foreach ($response as $index) {
                $this->line('- '.($index['index'] ?? '[unknown]'));
            }
        } catch (Throwable $e) {
            $this->error('Manticore list error: '.$e->getMessage());
        }
    }
}
