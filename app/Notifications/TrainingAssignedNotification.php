<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification; // training module

class TrainingAssignedNotification extends Notification
{
    use Queueable;

    public $package;

    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pelatihan Baru Telah Ditugaskan')
            ->greeting('Halo, '.$notifiable->name)
            ->line('Anda telah ditugaskan pelatihan berikut:')
            ->line('Judul: '.$this->package->title)
            ->line('Deskripsi: '.$this->package->description)
            ->action('Mulai Pelatihan', url('/training/packages/'.$this->package->id))
            ->line('Silakan selesaikan pelatihan ini sebelum deadline.')
            ->salutation('Terima kasih, Tim '.config('app.name'));
    }
}
