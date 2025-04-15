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

Publish the config file (optional):

```bash
php artisan vendor:publish --tag=config
```

---

## ⚙️ Configuration

In `config/scout.php`:

```php
'driver' => 'manticore',
```

In `.env` or `config/laravel_manticore.php`:

```env
LARAVEL_MANTICORE_HOST=127.0.0.1
LARAVEL_MANTICORE_PORT=9308
LARAVEL_MANTICORE_DEBUG=true
```

---

## 🏗 Making a Model Searchable

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
            'embedding' => $this->embedding,
        ];
    }
}
```

---

## 🛠 Index Management

Run once per model to initialize index:

```bash
php artisan manticore:create-index "App\Models\Post"
```

To sync field types after changes:

```bash
php artisan manticore:sync-index "App\Models\Post"
```

// Before running search, make sure your index is populated:
php artisan scout:import "App\\Models\\Post"

---

## 🔍 Basic Search Example

```php
Post::search('open education')->get();
```

---

## 🚀 Advanced Search

### With vector + filters

```php
use Ritey\LaravelManticore\FilterBuilder;

$filters = (new FilterBuilder)
    ->where('metadata.topic', 'ai')
    ->whereRange('score', ['gte' => 0.6]);

Post::search('ai in education')
    ->tap(function ($builder) use ($filters, $vector) {
        $builder->vector = $vector;
        $builder->filterBuilder = $filters;
        $builder->similarity = 'cosine';
    })
    ->get();
```

### Sorting

```php
Post::search('ai')
    ->tap(fn($b) => $b->sort = [['score' => 'desc']])
    ->get();
```

### Boosting Fields

```php
Post::search('language learning')
    ->tap(fn($b) => $b->boosts = ['title' => 2.0, 'body' => 1.0])
    ->get();
```

---

## 📊 Facets

```php
Post::search('climate')
    ->tap(fn($b) => $b->facets = ['metadata.topic'])
    ->get();
```

---

## 💡 Highlighting (Default Enabled)

Results include highlighting in `highlight` key.

---

## 🧪 Debugging

Enable logging by setting:

```env
LARAVEL_MANTICORE_DEBUG=true
```

Laravel will log connection config to the default logger.

---

## 📄 License

MIT License. (c) Ritey


---

## 🛠 Programmatic Index Management (Scout 10+)

In addition to Artisan commands, indexes can be created or deleted via Laravel Scout:

```php
use Laravel\Scout\EngineManager;

$engine = resolve(EngineManager::class)->engine('manticore');
$engine->createIndex('posts_index');
$engine->deleteIndex('posts_index');
```

