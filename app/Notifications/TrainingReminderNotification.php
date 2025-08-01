<?php

namespace App\Notifications;

use App\Models\AssignTraining;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrainingReminderNotification extends Notification
{
    use Queueable;

    public $assignTraining;
       public function __construct(AssignTraining $assignTraining)
    {
         $this->assignTraining = $assignTraining;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
         return (new MailMessage)
            ->subject('Reminder: Deadline Training Besok')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Ini pengingat bahwa kamu memiliki training yang akan berakhir BESOK.')
            ->line('📘 Training: ' . $this->assignTraining->package->name)
            ->line('⏰ Deadline: ' . \Carbon\Carbon::parse($this->assignTraining->deadline)->format('d M Y'))
            ->action('Kerjakan Sekarang', url('/training/packages/' . $this->assignTraining->package->id)) 
            ->line('Segera selesaikan sebelum waktu habis!');
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
