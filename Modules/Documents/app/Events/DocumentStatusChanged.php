<?php

namespace Modules\Documents\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Documents\Entities\Document;
use Modules\Documents\Entities\DocumentStatus;

class DocumentStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Document $document,
        public DocumentStatus $fromStatus,
        public DocumentStatus $toStatus,
        public string $reason = ''
    ) {}
}
