<?php

namespace App\Models\Document;

use Illuminate\Database\Eloquent\Model;

class DocumentStatus extends Model
{
    protected $fillable = [
        'key',
        'label',
        'description',
        'color',
        'icon',
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
