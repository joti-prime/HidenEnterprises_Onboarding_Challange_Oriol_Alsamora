<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationsController extends Controller
{
    public function markAllAsRead()
    {
        Notification::markAllAsRead();

        return redirect()->back()->with('success',
            trans('responses.mark_all_read_success',
                ['default' => 'Marked all notifications as read.'])
        );
    }
}
