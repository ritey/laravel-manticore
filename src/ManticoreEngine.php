<?php
/**
 * Laravel Manticore Scout
 * (c) Ritey, MIT License
 */


namespace Ritey\LaravelManticore;

use Laravel\Scout\Engines\Engine;
use Illuminate\Database\Eloquent\Collection;
use Manticoresearch\Client;

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
    {
                            try {
            foreach ($models as $model) {
            $index = $model->searchableAs();
            $data = $model->toSearchableArray();
            $data['id'] = $model->getKey();

            $this->client->index($index)->addDocuments([$data]);
        }
            } catch (\Throwable $e) {
            throw new \RuntimeException('Manticore update failed: ' . $e->getMessage());
        }
    }

    public function delete($models)
    {
                            try {
            foreach ($models as $model) {
            $index = $model->searchableAs();
            $this->client->index($index)->deleteDocument($model->getKey());
        }
            } catch (\Throwable $e) {
            throw new \RuntimeException('Manticore delete failed: ' . $e->getMessage());
        }
    }

    public function search($builder)
    {
        return $this->buildSearch($builder, $builder->limit ?? 10, 0);
    }

    public function paginate($builder, $perPage, $page)
    {
        $offset = ($page - 1) * $perPage;
        return $this->buildSearch($builder, $perPage, $offset);
            } catch (\Throwable $e) {
            throw new \RuntimeException('Manticore search query failed: ' . $e->getMessage());
        }
    }

    protected function buildSearch($builder, $size, $from)
    {
        try {
    {
        $index = $builder->model->searchableAs();
        $vector = $builder->vector ?? null;
        $similarity = $builder->similarity ?: config('manticore.similarity', 'dotproduct');
        $filterBuilder = $builder->filterBuilder ?? null;
        $sort = $builder->sort ?? null;
        $boosts = $builder->boosts ?? [];

        $queryBody = [];

        $mustClauses = [];

        if (!empty($boosts)) {
            foreach ($boosts as $field => $weight) {
                $mustClauses[] = ['match' => [$field => ['query' => $builder->query, 'boost' => $weight]]];
            }
        } elseif (!$vector) {
            $mustClauses[] = ['match' => ['*' => $builder->query]];
        }

        if ($vector) {
            $scriptScore = [
                'script_score' => [
                    'script' => [
                        'source' => "{$similarity}(embedding, params.query_vector)",
                        'params' => [
                            'query_vector' => $vector
                        ]
                    ]
                ]
            ];
            $mustClauses[] = $scriptScore;
        }

        $query = count($mustClauses) > 1 ? ['bool' => ['must' => $mustClauses]] : $mustClauses[0];

        if ($filterBuilder) {
            $filters = $filterBuilder->get();
            if (!empty($filters)) {
                $query = [
                    'bool' => [
                        'must' => [$query],
                        'filter' => $filters
                    ]
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
    
        $queryBody['highlight'] = [
            'fields' => ['*' => new \stdClass()]
        ];
    

        if ($sort && is_array($sort)) {
            $queryBody['sort'] = $sort;
        }

        return $this->client->search([
            'index' => $index,
            'body' => $queryBody,
        ]);
    }

    public function map($results, $model)
    {
        if (!isset($results['hits']['hits'])) {
            return Collection::make();
        }

        $ids = collect($results['hits']['hits'])->pluck('_id')->all();
        return $model->whereIn($model->getKeyName(), $ids)->get();
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
        // No bulk flush implemented
    }
}
