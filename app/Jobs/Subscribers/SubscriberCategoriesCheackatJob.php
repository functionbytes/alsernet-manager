<?php

namespace App\Jobs\Subscribers;

use App\Subscriber\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubscriberCategoriesCheackatJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subscriber;

    protected $categories;

    public function __construct(Subscriber $subscriber, $categories)
    {
        $this->subscriber = $subscriber;
        $this->categories = $categories;
    }

    public function handle()
    {
        $this->subscriber->suscriberCategoriesCheackatWithLog(
            $this->categories,
        );
    }
}
