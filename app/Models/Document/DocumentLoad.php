<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Model;

class DocumentLoad extends Model
{
    protected $fillable = [
        'key',
        'label',
        'description',
        'icon',
        'color',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'order' => 'integer',
        ];
    }
}
