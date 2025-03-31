# Laravel Manticore Scout — Setup Guide

This guide helps you configure Manticore Search indexes and keep them updated using Laravel Eloquent + Scout.

---

## 🏗 Step 1: Create a Manticore Index (Schema)

To use Laravel Scout, you need to define a **table (index)** in Manticore that matches your searchable model.

### 🔧 Example: Creating a real-time index

```sql
CREATE TABLE posts_index (
    id BIGINT,
    title TEXT,
    body TEXT,
    created_at TIMESTAMP,
    metadata JSON,
    embedding VECTOR(384) TYPE FLOAT,
    INDEX title title,
    INDEX metadata_topic metadata.topic,
    INDEX metadata_level metadata.level
);
```

> ✅ Tip: Use `rt` (real-time) tables so you can insert/update/delete from Laravel.

---

## 🧪 Step 2: Define Searchable Model in Laravel

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
            'created_at' => $this->created_at->timestamp,
            'metadata' => $this->metadata,
            'embedding' => $this->embedding, // Array of floats
        ];
    }
}
```

---

## 🔁 Step 3: Sync Data with Laravel

To import existing data:

```bash
php artisan scout:import "App\Models\Post"
```

> ℹ️ This will loop through all `Post` records and index them into Manticore.

---

## 🔄 Step 4: Keep Indexes Up-to-Date Automatically

Laravel Scout will automatically update/delete Manticore documents when your model changes.

### Example:

```php
$post = Post::find(1);
$post->title = "Updated title";
$post->save(); // Scout updates the index

$post->delete(); // Scout deletes from index
```

---

## ⚙️ Config

Update `config/scout.php`:

```php
'driver' => 'manticore',
```

And optionally publish `manticore.php`:

```bash
php artisan vendor:publish --tag=config
```

Then set your host and port:

```php
return [
    'host' => env('MANTICORE_HOST', '127.0.0.1'),
    'port' => env('MANTICORE_PORT', 9308),
];
```

---

## 🧼 Optional: Clear or Rebuild Index

To rebuild:

```bash
php artisan scout:flush "App\Models\Post"
php artisan scout:import "App\Models\Post"
```

> ⚠️ This removes and re-adds all documents in the index.

---

Need help automating index creation via migrations or CLI? Ping the maintainer!


---

## 🧩 Faceting Support

You can request facets (aggregations) by passing:

```php
$builder->facets = ['type', 'metadata.topic'];
```

This will return doc counts for each unique value in those fields.
