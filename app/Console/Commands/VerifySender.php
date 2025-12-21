<?php

namespace App\Console\Commands;

use Acelle\Model\Sender;
use Illuminate\Console\Command;

class VerifySender extends Command
{
    protected $signature = 'sender:verify';

    protected $description = 'Verify Sender';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $senders = Sender::pending()->get();
        foreach ($senders as $sender) {
            $sender->updateVerificationStatus();
        }

        return 0;
    }
}
