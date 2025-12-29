<?php

namespace Modules\Documents\Entities;

use App\Models\Lang;
use App\Models\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRequirementTranslation extends Model
{
    use HasUid;

    protected $table = 'document_type_requirement_translations';

    protected $fillable = [
        'uid',
        'document_requirement_id',
        'lang_id',
        'name',
        'help_text',
    ];

    public function documentRequirement(): BelongsTo
    {
        return $this->belongsTo(DocumentRequirement::class);
    }

    public function lang(): BelongsTo
    {
        return $this->belongsTo(Lang::class);
    }
}
