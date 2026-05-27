<?php

namespace App\Notifications;

use App\Modules\Shifts\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestRejected extends Notification
{
    use Queueable;

    public function __construct(public readonly LeaveRequest $leave) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $message = sprintf(
            'Votre demande de congé du %s au %s a été refusée.',
            $this->leave->start_date->format('d/m/Y'),
            $this->leave->end_date->format('d/m/Y')
        );

        if ($this->leave->rejection_reason) {
            $message .= ' Motif : ' . $this->leave->rejection_reason;
        }

        return [
            'type'     => 'leave_rejected',
            'message'  => $message,
            'leave_id' => $this->leave->id,
            'url'      => route('shifts.leaves.index'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Demande de congé refusée')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line(sprintf(
                'Votre demande de congé du %s au %s a été refusée.',
                $this->leave->start_date->format('d/m/Y'),
                $this->leave->end_date->format('d/m/Y')
            ));

        if ($this->leave->rejection_reason) {
            $mail->line('Motif : ' . $this->leave->rejection_reason);
        }

        return $mail
            ->action('Voir mes congés', route('shifts.leaves.index'))
            ->line('Cordialement,');
    }
}
