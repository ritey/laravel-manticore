<?php

/**
 * Laravel Manticore Scout
 * (c) Ritey, MIT License.
 */

namespace Ritey\LaravelManticore;

use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use Manticoresearch\Client;

class ManticoreEngine extends Engine
{
    protected Client $client;

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
                $data['id'] = $model->getScoutKey(); // Uses proper key
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

    public function search(Builder $builder)
    {
        return $this->buildSearch($builder, $builder->limit ?? 10, 0);
    }

    public function paginate(Builder $builder, $perPage, $page)
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

        $ids = collect($results['hits']['hits'])->pluck('_id')->all();

        return $model->whereIn($model->getScoutKeyName(), $ids)->get();
    }

    public function mapIds($results)
    {
        return collect($results['hits']['hits'])->pluck('_id')->values();
    }

    public function getTotalCount($results)
    {
        return $results['hits']['total']['value'] ?? 0;
    }

    public function flush($model)
    {
        // Not implemented: bulk delete
    }

    public function createIndex($name, array $options = [])
    {
        try {
            return $this->client->tables()->create([
                'index' => $name,
                'body' => $options,
            ]);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Failed to create index '{$name}': ".$e->getMessage());
        }
    }

    public function deleteIndex($name)
    {
        try {
            return $this->client->tables()->drop(['index' => $name]);
        } catch (\Throwable $e) {
            throw new \RuntimeException("Failed to delete index '{$name}': ".$e->getMessage());
        }
    }

    protected function buildSearch(Builder $builder, int $size, int $from)
    {
        $index = $builder->model->searchableAs();
        $vector = $builder->vector ?? null;
        $similarity = $builder->similarity ?? config('laravel_manticore.similarity', 'dotproduct');
        $filterBuilder = $builder->filterBuilder ?? null;
        $sort = $builder->sort ?? null;
        $boosts = $builder->boosts ?? [];

        $queryBody = [];
        $mustClauses = [];

        if (!empty($boosts)) {
            foreach ($boosts as $field => $weight) {
                $mustClauses[] = ['match' => [$field => ['query' => $builder->query, 'boost' => $weight]]];
            }
        } elseif (!$vector && !empty($builder->query)) {
            $mustClauses[] = ['match' => ['*' => $builder->query]];
        }

        if (empty($mustClauses)) {
            $mustClauses[] = ['match_all' => new \stdClass()];
        }

        if ($vector) {
            $mustClauses[] = [
                'script_score' => [
                    'script' => [
                        'source' => "{$similarity}(embedding, params.query_vector)",
                        'params' => [
                            'query_vector' => $vector,
                        ],
                    ],
                ],
            ];
        }

        $query = count($mustClauses) > 1 ? ['bool' => ['must' => $mustClauses]] : $mustClauses[0];

        if ($filterBuilder) {
            $filters = $filterBuilder->get();
            if (!empty($filters)) {
                $query = [
                    'bool' => [
                        'must' => [$query],
                        'filter' => $filters,
                    ],
                ];
            }
        }

        $queryBody['query'] = $query;
        $queryBody['size'] = $size;
        $queryBody['from'] = $from;

        if (!empty($builder->facets)) {
            $queryBody['aggs'] = [];
            foreach ($builder->facets as $facetField) {
                $queryBody['aggs'][$facetField] = ['terms' => ['field' => $facetField]];
            }
        }

        $queryBody['highlight'] = ['fields' => ['*' => new \stdClass()]];

        if ($sort && is_array($sort)) {
            $queryBody['sort'] = $sort;
        }

        return $this->client->search([
            'table' => $index,
            'body' => $queryBody,
        ]);
    }
}
