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

            // Confirm the index exists before attempting delete
            $existing = $client->indices()->show();
            if (!collect($existing)->pluck('index')->contains($index)) {
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
