<?php

namespace App\Notifications;

use App\Mail\UserLogInMail;
use App\Models\Noti;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use App\Notifications\Channels\WebPushChannel;


class UserLoginNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;
    public $device_info;
    public $ip_address;

    /**
     * Create a new notification instance.
     */
    public function __construct($device_info, $ip_address)
    {
        $this->device_info = $device_info;
        $this->ip_address = $ip_address;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class, "mail", "database"];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): UserLogInMail
    {
        return (new UserLogInMail($notifiable, $this->device_info, $this->ip_address))->to($notifiable->email);
    }

    public function toDatabase(object $notifiable): array
    {

        return ([
            'user_id' => $notifiable->id,
            'message' => 'You account has been logged in from device ' . $this->device_info . ' with IP address ' . $this->ip_address . ' at ' . now()->format('Y-m-d H:i:s'),
            'type' => 'important',
            'read' => false,
            'action' => 'It is not me',
            'action_url' => 'https://example.com/login',
            'title' => 'Login Notification',
        ]);
    }

 

    public function toWebPush($notifiable)
    {
        $auth = [
            'VAPID' => [
                'subject' => 'mailto:admin@example.com',
                'publicKey' => config('webpush.vapid.public_key'),
                'privateKey' => config('webpush.vapid.private_key'),
            ],
        ];

        $webPush = new WebPush($auth);

        foreach ($notifiable->subscribe as $sub) {
            $subscription = Subscription::create([
                'endpoint' => $sub->endpoint,
                'publicKey' => $sub->p256dh,   
                'authToken' => $sub->auth,     
                'contentEncoding' => 'aes128gcm',
            ]);

            $payload = json_encode([
                'title' => 'Login Notification',
                'body' => 'Your account was logged in from device ' . $this->device_info,
                'url' => config('app.url') . '/notifications',
            ]);

            $webPush->sendOneNotification($subscription, $payload);
        }

        return true;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
