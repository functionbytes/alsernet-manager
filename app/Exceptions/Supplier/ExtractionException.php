<?php

namespace App\Exceptions\Supplier;

use Exception;

/**
 * Exception thrown when extraction operations fail.
 */
class ExtractionException extends Exception
{
    public static function batchNotFound(string $batchId): self
    {
        return new self("Extraction batch not found: {$batchId}");
    }

    public static function invalidBatchStatus(string $status): self
    {
        return new self("Invalid batch status: {$status}");
    }

    public static function mappingNotFound(int $sourceId): self
    {
        return new self("No active extraction mapping found for source ID: {$sourceId}");
    }

    public static function invalidExtractionData(string $reason): self
    {
        return new self("Invalid extraction data: {$reason}");
    }

    public static function orchestratorConnectionFailed(string $reason): self
    {
        return new self("Failed to connect to orchestrator: {$reason}");
    }

    public static function workflowNotFound(string $workflowType): self
    {
        return new self("Workflow not found for type: {$workflowType}");
    }
}
