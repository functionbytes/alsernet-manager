<?php

namespace Modules\Document\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Document\Entities\Document;

class DocumentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Document $document) {}
}
