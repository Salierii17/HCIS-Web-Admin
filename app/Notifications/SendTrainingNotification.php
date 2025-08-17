<?php

namespace App\Notifications;

use App\Settings\GeneralSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class SendTrainingNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $packageName;

    protected ?string $company_name;

    public function __construct($packageName)
    {
        $this->packageName = $packageName;
        $this->company_name = (new GeneralSetting)->company_name;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Training Assigned: '.$this->packageName)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('You have been assigned to a training package: '.$this->packageName)
            ->action('View Training', url('/')) // You can change this URL to the specific training page
            ->line('Thank you for your participation.')
            ->salutation(new HtmlString("Regards,<br/>{$this->company_name}"));
    }

    /**
     * Get the array representation of the notification for the database.
     * This is what the mobile app will receive.
     */
    public function toArray(object $notifiable): array
    {
        // This remains concise for the in-app notification card.
        return [
            'title' => 'New Training Assigned',
            'message' => 'Package: '.$this->packageName,
        ];
    }
}
