<?php

namespace App\Listeners\Campaigns;

use App\Events\Campaigns\GiftvoucherCreated;
use App\Mail\Campaigns\Giftvoucher\GiftvoucherMail;
use App\Traits\PreventsDuplicateEventExecution;
use Illuminate\Support\Facades\Mail;

class GiftvoucherListener
{
    use PreventsDuplicateEventExecution;

    public function handle(GiftvoucherCreated $event): void
    {
        // Prevent duplicate execution within the same request
        if ($this->preventDuplicateExecution($event)) {
            return;
        }

        $this->handleMailGiftvoucher($event);
    }

    public function handleMailGiftvoucher(GiftvoucherCreated $event)
    {
        $newsletter = $event->newsletter;
        $email = $newsletter->email;
        $mail = new GiftvoucherMail($newsletter);
        Mail::to($email)->queue($mail);
    }
}
