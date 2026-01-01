<?php

namespace Modules\Document\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Document\Entities\Document;
use Modules\Document\Entities\DocumentStatus;

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
