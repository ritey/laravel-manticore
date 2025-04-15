<?php

/**
 * Laravel Manticore Scout
 * (c) Ritey, MIT License.
 */

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

            // Use updated tables() method
            $client->tables()->drop(['index' => $index]);

            $this->info("Index '{$index}' deleted successfully.");
        } catch (\Throwable $e) {
            $this->error('Manticore delete error: '.$e->getMessage());
        }
    }
}
