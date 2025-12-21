<?php

namespace App\Events\Supplier;

use App\Events\Event;
use App\Models\Supplier\SupplierAutomationExecution;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when an orchestrator workflow is triggered.
 */
class OrchestratorWorkflowTriggered extends Event
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SupplierAutomationExecution $execution,
        public string $workflowType
    ) {}
}
