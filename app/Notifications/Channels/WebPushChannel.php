<?php

namespace App\Notifications\Channels;

class WebPushChannel
{
    public function send($notifiable, $notification)
    {
        if (method_exists($notification, 'toWebPush')) {
            return $notification->toWebPush($notifiable);
        }

        return null;
    }
}
