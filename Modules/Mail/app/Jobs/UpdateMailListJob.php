<?php

namespace Modules\Mail\Jobs;

use App\Model\Blacklist;
use App\Model\MailList;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpdateMailListJob extends Base implements ShouldBeUnique
{
    public $list;

    public $uniqueFor = 3600;

    public function __construct(MailList $list)
    {
        $this->list = $list;
    }

    public function handle()
    {
        $this->list->updateCachedInfo();
        Blacklist::doBlacklist($this->list->customer);
    }

    public function uniqueId()
    {
        return $this->list->id;
    }
}
