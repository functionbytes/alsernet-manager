<?php

namespace App\Listeners\Subscribers;

use App\Events\Campaigns\MailListSubscription;
use App\Events\Campaigns\MailListUnsubscription;
use App\Models\Setting;

class SendListNotificationToOwner
{
    public function __construct() {}

    public function handleMailListSubscription(MailListSubscription $event)
    {
        $subscriber = $event->subscriber;
        $list = $subscriber->mailList;
        $user = $list->customer->user;

        if (Setting::isYes('send_notification_mail_for_list_subscription')) {
            $list->sendSubscriptionNotificationEmailToListOwner($subscriber);
        }
    }

    public function handleMailListUnsubscription(MailListUnsubscription $event)
    {
        $subscriber = $event->subscriber;
        $list = $subscriber->mailList;

        if (Setting::isYes('send_notification_mail_for_list_subscription')) {
            $list->sendUnsubscriptionNotificationEmailToListOwner($subscriber);
        }
    }

    public function subscribe($events)
    {
        $events->listen(
            'App\Events\Campaigns\MailListSubscription',
            [SendListNotificationToOwner::class, 'handleMailListSubscription']
        );

        $events->listen(
            'App\Events\Campaigns\MailListUnsubscription',
            [SendListNotificationToOwner::class, 'handleMailListUnsubscription']
        );
    }
}
