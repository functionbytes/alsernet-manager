<?php

namespace App\Events\Document;

use App\Models\Document\Document;
use App\Models\Document\DocumentStatus;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentStatusChanged
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Document $document,
        public DocumentStatus $fromStatus,
        public DocumentStatus $toStatus,
        public ?string $reason = null,
    ) {
        //
    }
}
