<?php

namespace App\Console\Commands;

use App\Services\Carriers\CarrierService;
use Illuminate\Console\Command;

class UpdateTrackingStatuses extends Command
{
    protected $signature = 'returns:update-tracking';

    protected $description = 'Update tracking statuses for all active pickup requests';

    protected $carrierService;

    public function __construct(CarrierService $carrierService)
    {
        parent::__construct();
        $this->carrierService = $carrierService;
    }

    public function handle()
    {
        $this->info('Updating tracking statuses...');

        try {
            $this->carrierService->updateTrackingStatuses();
            $this->info('Tracking statuses updated successfully.');
        } catch (\Exception $e) {
            $this->error('Error updating tracking statuses: '.$e->getMessage());
        }
    }
}
