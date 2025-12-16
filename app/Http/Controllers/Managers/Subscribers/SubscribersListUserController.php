<?php

namespace App\Http\Controllers\Managers\Subscribers;

use App\Http\Controllers\Controller;
use App\Models\Subscriber\NewsletterLIstUser;

class SubscribersListUserController extends Controller
{
    public function destroy($uid)
    {
        $list = null;
        $item = NewsletterListUser::uid($uid);
        $list = $item->list->uid;
        $item->delete();

        return redirect()->route('manager.subscribers.lists.details', $list);
    }
}
