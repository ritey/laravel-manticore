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

    'defaults' => [
        'index_settings' => [
            'ngram_chars' => 'utf8',
            'charset_table' => env('MANTICORE_CHARSET_TABLE', 'chinese'),
            'morphology' => env('MANTICORE_MORPHOLOGY', 'icu_chinese'),
            'ngram_len' => env('MANTICORE_NGRAM_LEN', 0),
            'min_infix_len' => env('MANTICORE_MIN_INFIX_LEN', 1),
            'prefix_fields' => [],
        ],
    ],
];
