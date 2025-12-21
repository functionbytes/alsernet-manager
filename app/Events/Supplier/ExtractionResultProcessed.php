<?php

namespace App\Events\Supplier;

use App\Events\Event;
use App\Models\Supplier\SupplierExtractionResult;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when an extraction result is processed.
 */
class ExtractionResultProcessed extends Event
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SupplierExtractionResult $result,
        public string $changeType
    ) {}
}
