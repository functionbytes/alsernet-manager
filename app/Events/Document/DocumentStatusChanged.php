<?php

namespace App\Events\Document;

use App\Models\Document\Document;
use App\Models\Document\DocumentStatus;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
