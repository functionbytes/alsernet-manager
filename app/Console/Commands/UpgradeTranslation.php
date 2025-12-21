<?php

namespace App\Console\Commands;

use Acelle\Model\Language;
use Illuminate\Console\Command;

class UpgradeTranslation extends Command
{
    protected $signature = 'translation:upgrade';

    protected $description = 'Update translation files to make those up-to-date with the default EN language';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        \Acelle\Helpers\pcopy(resource_path('lang/en'), resource_path('lang/default'));
        Language::dump();
    }
}
