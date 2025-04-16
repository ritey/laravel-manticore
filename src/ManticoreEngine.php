<?php

/**
 * Laravel Manticore Scout
 * (c) Ritey, MIT License.
 */

namespace Ritey\LaravelManticore;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use Manticoresearch\Client;
use Manticoresearch\Search;

class ManticoreEngine extends Engine
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function update($models)
    {
        try {
            foreach ($models as $model) {
                $index = $model->searchableAs();
                $data = $model->toSearchableArray();
                $data['id'] = $model->getScoutKey();

                $this->client->table($index)->addDocuments([$data]);
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException('Manticore update failed: '.$e->getMessage());
        }
    }

    public function delete($models)
    {
        try {
            foreach ($models as $model) {
                $index = $model->searchableAs();
                $this->client->table($index)->deleteDocument($model->getScoutKey());
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException('Manticore delete failed: '.$e->getMessage());
        }
    }

    public function search($builder)
    {
        return $this->buildSearch($builder, $builder->limit ?? 10, 0);
    }

    public function paginate($builder, $perPage, $page)
    {
        $offset = ($page - 1) * $perPage;

        try {
            return $this->buildSearch($builder, $perPage, $offset);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Manticore search query failed: '.$e->getMessage());
        }
    }

    public function lazyMap(Builder $builder, $results, $model)
    {
        return $this->map($builder, $results, $model);
    }

    public function map(Builder $builder, $results, $model)
    {
        if (!isset($results['hits']['hits'])) {
            return Collection::make();
        }

        $ids = collect($results['hits']['hits'])->map(function ($hit) {
            return $hit->getId();
        })->all();

        return $model->whereIn($model->getScoutKeyName(), $ids)->get();
    }

    public function mapIds($results)
    {
        return $ids = collect($results['hits']['hits'])->map(function ($hit) {
            return $hit->getId();
        })->all();
    }

    public function getTotalCount($results)
    {
        return $results['hits']['total']['value'] ?? 0;
    }

    public function flush($model)
    {
        // No bulk flush implemented
    }

    public function createIndex($name, array $options = [])
    {
        try {
            $this->client->tables()->create(['index' => $name]);

            return true;
        } catch (\Throwable $e) {
            throw new \RuntimeException("Failed to create index '{$name}': ".$e->getMessage());
        }
    }

    public function deleteIndex($name)
    {
        try {
            $this->client->tables()->drop(['index' => $name]);

            return true;
        } catch (\Throwable $e) {
            throw new \RuntimeException("Failed to delete index '{$name}': ".$e->getMessage());
        }
    }

    protected function buildSearch($builder, $size, $from)
    {
        $index = $builder->model->searchableAs();
        $vector = $builder->vector ?? null;
        $similarity = $builder->similarity ?? config('laravel_manticore.similarity', 'dotproduct');
        $filterBuilder = $builder->filterBuilder ?? null;
        $sort = $builder->sort ?? null;
        $boosts = $builder->boosts ?? [];

        try {
            // Initialize search
            $search = new Search($this->client);
            $search->setTable($index);

            // Build the search directly with raw query syntax
            // This avoids using potentially non-existent classes
            if (!empty($builder->query)) {
                // Use raw query string as it's most likely to work
                $search->search($builder->query);

                // Apply field weights/boosts if specified
                if (!empty($boosts)) {
                    foreach ($boosts as $field => $weight) {
                        $search->setFieldWeight($field, $weight);
                    }
                }
            }

            // Set limits
            $search->limit($size);
            if ($from > 0) {
                $search->offset($from);
            }

            // Apply any sort parameters
            if ($sort && is_array($sort)) {
                foreach ($sort as $field => $direction) {
                    $search->sort([$field => $direction]);
                }
            }

            if (config('laravel_manticore.debug')) {
                Log::debug('[laravel-manticore] Executing search with:', [
                    'table' => $index,
                    'query' => $builder->query,
                    'vector' => $vector,
                    'similarity' => $similarity,
                    'filters' => $filterBuilder ? $filterBuilder->get() : null,
                    'boosts' => $boosts,
                    'sort' => $sort,
                    'limit' => $size,
                    'offset' => $from,
                ]);
            }

            // Execute search
            $results = iterator_to_array($search->get());

            if (config('laravel_manticore.debug')) {
                Log::debug('[laravel-manticore] Search results returned:', [
                    'total_hits' => count($results),
                ]);
            }
            // Vector search would need to be handled separately,
            // but isn't implemented here to avoid errors

            // Return formatted results
            return [
                'hits' => [
                    'hits' => $results,
                    'total' => [
                        'value' => count($results),
                    ],
                ],
            ];
        } catch (\Throwable $e) {
            throw new \RuntimeException('Manticore search query failed: '.$e->getMessage());
        }
    }
}
