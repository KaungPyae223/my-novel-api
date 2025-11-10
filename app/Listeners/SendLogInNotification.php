<?php

namespace App\Listeners;

use App\Events\LogInEvent;
use App\Http\Utils\WriteLog;
use App\Notifications\UserLoginNotification;
use Illuminate\Support\Facades\Log;

class SendLogInNotification 
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LogInEvent $event): void
    { 

        WriteLog::writeUserLog("User Login",$event->user,"authentication",json_encode([
            "device_info" => $event->device_info,
            "ip_address" => $event->ip_address,
            "login_at" => now()->format('Y-m-d H:i:s'),
        ]));

        $event->user->notify(new UserLoginNotification($event->device_info, $event->ip_address));
    }
}
