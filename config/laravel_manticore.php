<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Manticore Host Configuration
    |--------------------------------------------------------------------------
    */

    'host' => env('MANTICORE_HOST', '127.0.0.1'),
    'port' => env('MANTICORE_PORT', 9308),

    /*
    |--------------------------------------------------------------------------
    | Default Similarity Function for Vector Search
    |--------------------------------------------------------------------------
    */

    'similarity' => env('MANTICORE_SIMILARITY', 'dotproduct'),

    /*
    |--------------------------------------------------------------------------
    | Default Vector Field Name
    |--------------------------------------------------------------------------
    */

    'vector_field' => env('MANTICORE_VECTOR_FIELD', 'embedding'),

    /*
    |--------------------------------------------------------------------------
    | Chunk Size for Bulk Import
    |--------------------------------------------------------------------------
    |
    | When using `scout:import`, this defines how many records are processed
    | in a single chunk to reduce memory usage.
    |
    */

    'import_chunk_size' => env('MANTICORE_IMPORT_CHUNK_SIZE', 500),

    'debug' => env('MANTICORE_DEBUG', false),
];
