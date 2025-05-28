<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendTrainingNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $packageName;

    public function __construct($packageName)
    {
        $this->packageName = $packageName;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Training Assignment Notification')
            ->greeting('Hello ' . $notifiable->name)
            ->line('You have been assigned to a training package: ' . $this->packageName)
            ->action('View Training', url('/')) // ubah URL sesuai kebutuhan
            ->line('Thank you for your participation.');
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
