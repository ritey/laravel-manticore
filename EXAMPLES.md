# Laravel Manticore Scout — Example Usage

## Basic Full-text Search

```php
Post::search('climate change')->get();
```

## Vector Search

```php
Post::search('')
    ->withVector($vectorArray)
    ->tap(function ($builder) {
        $builder->similarity = 'cosine';
    })
    ->get();
```

## JSON Filter Search

```php
use Ritey\LaravelManticore\FilterBuilder;

$filters = (new FilterBuilder)
    ->where('metadata.topic', 'science')
    ->whereRange('metadata.score', ['gte' => 0.6]);

Post::search('')
    ->tap(function ($builder) use ($filters) {
        $builder->filterBuilder = $filters;
    })
    ->get();
```

## Hybrid Search with Boosting + Vector

```php
Post::search('climate')
    ->tap(function ($builder) use ($vector, $filters) {
        $builder->vector = $vector;
        $builder->similarity = 'dotproduct';
        $builder->boosts = [
            'title' => 3,
            'summary' => 2,
        ];
        $builder->filterBuilder = $filters;
        $builder->sort = [['created_at' => 'desc']];
    })
    ->paginate(20);
```

## Sorting Results

```php
Post::search('ai')
    ->tap(function ($builder) {
        $builder->sort = [['created_at' => 'desc']];
    })
    ->get();
```


---

## 🧩 Faceting (Aggregations)

```php
Post::search('education')
    ->tap(function ($builder) {
        $builder->facets = ['metadata.topic', 'type'];
    })
    ->get();
```

In the response from Manticore, you’ll find:

```json
'aggs' => [
    'metadata.topic' => [
        ['key' => 'ai', 'doc_count' => 150],
        ['key' => 'climate', 'doc_count' => 85],
    ],
    'type' => [
        ['key' => 'article', 'doc_count' => 120],
        ['key' => 'video', 'doc_count' => 35],
    ]
]
```


---

## ✨ Highlighting & Facets Example

```php
$results = Post::search('climate')
    ->tap(function ($builder) {
        $builder->facets = ['metadata.topic'];
    })
    ->get();

foreach ($results as $result) {
    echo $result->highlight['title'][0] ?? '';
}

$facets = $results->raw()['aggregations'] ?? [];
```
