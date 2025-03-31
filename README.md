# Laravel Manticore Scout

A Laravel Scout engine for [Manticore Search](https://manticoresearch.com), supporting:

- ✅ Full-text search
- ✅ Vector similarity search
- ✅ JSON field filters
- ✅ Field boosting
- ✅ Hybrid search (vector + full-text)
- ✅ Pagination and sorting
- ✅ Artisan commands to manage Manticore indexes

---

## 🔧 Installation

```bash
composer require ritey/laravel-manticore
```

Publish config (optional):

```bash
php artisan vendor:publish --tag=config
```

---

## ⚙️ Configuration

In `config/scout.php`:

```php
'driver' => 'manticore',
```

In `.env` or `config/manticore.php`:

```env
MANTICORE_HOST=127.0.0.1
MANTICORE_PORT=9308
```

---

## 📦 Usage

### Make a model searchable

```php
use Laravel\Scout\Searchable;

class Post extends Model
{
    use Searchable;

    public function searchableAs(): string
    {
        return 'posts_index';
    }

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'metadata' => $this->metadata,
            'embedding' => $this->embedding, // vector array
        ];
    }
}
```

---

### Search with vector + filters + sorting

```php
use Ritey\LaravelManticore\FilterBuilder;

$filters = (new FilterBuilder)
    ->where('metadata.topic', 'ai')
    ->whereRange('score', ['gte' => 0.6]);

Post::search('ai in education')
    ->tap(function ($builder) use ($filters, $vector) {
        $builder->vector = $vector;
        $builder->boosts = ['title' => 2.5];
        $builder->filterBuilder = $filters;
        $builder->sort = [['created_at' => 'desc']];
    })
    ->paginate(10);
```

---

## 🛠 Artisan Commands

```bash
php artisan manticore:create-index "App\Models\Post"
php artisan manticore:sync-index "App\Models\Post"
```

---

## 📜 License

This package is open-sourced software licensed under the [MIT license](LICENSE).


---

## 🧩 Faceting Support

You can request facets (aggregations) by passing:

```php
$builder->facets = ['type', 'metadata.topic'];
```

This will return doc counts for each unique value in those fields.
