<?php

namespace App\Models\Document;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentValidationHistory extends Model
{
    protected $table = 'document_validation_history';

    protected $fillable = [
        'document_id',
        'stage_number',
        'validator_group',
        'validator_user_id',
        'action',
        'comments',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'validated_at' => 'datetime',
            'stage_number' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Document relationship
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    /**
     * Validator user relationship
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validator_user_id');
    }
}
