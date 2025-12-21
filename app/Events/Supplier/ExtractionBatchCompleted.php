<?php

namespace App\Events\Supplier;

use App\Events\Event;
use App\Models\Supplier\SupplierExtractionBatch;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when an extraction batch completes.
 */
class ExtractionBatchCompleted extends Event
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SupplierExtractionBatch $batch
    ) {}
}
