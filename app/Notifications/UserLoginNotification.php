<?php

namespace App\Notifications;

use App\Mail\UserLogInMail;
use App\Models\Noti;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

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
        return ['database'];
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

        return([
            'user_id' => $notifiable->id,
            'message' => 'You account has been logged in from device ' . $this->device_info . ' with IP address ' . $this->ip_address . ' at ' . now()->format('Y-m-d H:i:s'),
            'type' => 'important',
            'read' => false,
            'action' => 'It is not me',
            'action_url' => 'https://example.com/login',
            'title' => 'Login Notification',
        ]);

       
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
