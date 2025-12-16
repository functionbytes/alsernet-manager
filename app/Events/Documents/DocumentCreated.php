<?php

namespace App\Events\Documents;

use App\Models\Document\Document;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Document $document)
    {
    }
}
