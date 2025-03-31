<?php

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
        foreach ($models as $model) {
            $index = $model->searchableAs();
            $data = $model->toSearchableArray();
            $data['id'] = $model->getKey();

            $this->client->index($index)->addDocuments([$data]);
        }
    }

    public function delete($models)
    {
        foreach ($models as $model) {
            $index = $model->searchableAs();
            $this->client->index($index)->deleteDocument($model->getKey());
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
    }

    protected function buildSearch($builder, $size, $from)
    {
        $index = $builder->model->searchableAs();
        $vector = $builder->vector ?? null;
        $similarity = $builder->similarity ?? 'dotproduct';
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
