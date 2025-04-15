<?php

/**
 * Laravel Manticore Scout
 * (c) Ritey, MIT License.
 */

namespace Ritey\LaravelManticore\Console;

use Illuminate\Console\Command;
use Manticoresearch\Client;

class ListManticoreIndexes extends Command
{
    protected $signature = 'manticore:list-indexes';
    protected $description = 'List all Manticore indexes (tables) available';

    public function handle()
    {
        try {
            $client = app(Client::class);
            $response = $client->sql('SHOW TABLES');

            if (empty($response)) {
                $this->info('No indexes (tables) found.');

                return;
            }

            foreach ($response as $row) {
                $this->line('- '.($row['Index'] ?? '[unknown]'));
            }
        } catch (\Throwable $e) {
            $this->error('Manticore list error: '.$e->getMessage());
        }
    }
}
