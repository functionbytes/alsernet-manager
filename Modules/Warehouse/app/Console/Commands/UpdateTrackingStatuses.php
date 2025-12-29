<?php

namespace Modules\Warehouse\Console\Commands;

use Modules\Returns\Services\CarrierService;
use Illuminate\Console\Command;

class UpdateTrackingStatuses extends Command
{
    protected $signature = 'returns:update-tracking';

    protected $description = 'Update tracking statuses for all active pickup requests';

    public function __construct(private CarrierService $carrierService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Updating tracking statuses...');

        try {
            $this->carrierService->updateTrackingStatuses();
            $this->info('Tracking statuses updated successfully.');

            return 0;
        } catch (\Exception $e) {
            $this->error('Error updating tracking statuses: '.$e->getMessage());

            return 1;
        }
    }
}
