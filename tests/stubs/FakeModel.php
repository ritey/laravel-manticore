<?php

/**
 * Laravel Manticore Scout
 * (c) Ritey, MIT License.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class FakeModel extends Model
{
    use Searchable;

    public function searchableAs(): string
    {
        return 'fake_model_index';
    }

    public function toSearchableArray(): array
    {
        return [
            'title' => 'Test',
            'score' => 1.23,
        ];
    }
}
