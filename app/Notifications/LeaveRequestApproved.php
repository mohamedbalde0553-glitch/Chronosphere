<?php

namespace App\Notifications;

use App\Modules\Shifts\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestApproved extends Notification
{
    use Queueable;

    public function __construct(public readonly LeaveRequest $leave) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'     => 'leave_approved',
            'message'  => sprintf(
                'Votre demande de congé du %s au %s a été approuvée.',
                $this->leave->start_date->format('d/m/Y'),
                $this->leave->end_date->format('d/m/Y')
            ),
            'leave_id' => $this->leave->id,
            'url'      => route('shifts.leaves.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Demande de congé approuvée')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line(sprintf(
                'Votre demande de congé du %s au %s a été approuvée.',
                $this->leave->start_date->format('d/m/Y'),
                $this->leave->end_date->format('d/m/Y')
            ))
            ->action('Voir mes congés', route('shifts.leaves.index'))
            ->line('Bonne journée !');
    }
}
