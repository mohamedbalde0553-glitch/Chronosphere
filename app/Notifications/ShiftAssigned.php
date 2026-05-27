<?php

namespace App\Notifications;

use App\Modules\Shifts\Models\Shift;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShiftAssigned extends Notification
{
    use Queueable;

    public function __construct(public readonly Shift $shift) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'shift_assigned',
            'message' => sprintf(
                'Un shift vous a été assigné le %s de %s à %s.',
                $this->shift->start_at->format('d/m/Y'),
                $this->shift->start_at->format('H:i'),
                $this->shift->end_at->format('H:i')
            ),
            'shift_id' => $this->shift->id,
            'url'      => route('shifts.planning'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouveau shift assigné')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line(sprintf(
                'Un shift vous a été assigné le %s de %s à %s.',
                $this->shift->start_at->format('d/m/Y'),
                $this->shift->start_at->format('H:i'),
                $this->shift->end_at->format('H:i')
            ))
            ->action('Voir le planning', route('shifts.planning'))
            ->line('Merci,');
    }
}
