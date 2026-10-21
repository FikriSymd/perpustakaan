<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationReadyNotification extends Notification
{
    use Queueable;

    public function __construct(public Reservation $reservation)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reservation = $this->reservation->loadMissing('book');
        $holdDays = $reservation->holdDaysLeft() ?? Reservation::HOLD_DAYS;

        return (new MailMessage)
            ->subject('Reservasi siap diambil: "'.$reservation->book->title.'"')
            ->view('mail.reservation-ready', [
                'name' => $notifiable->name,
                'memberCode' => $notifiable->member_code,
                'bookTitle' => $reservation->book->title,
                'bookAuthor' => $reservation->book->author,
                'holdDays' => $holdDays,
                'expiresAt' => $reservation->expires_at,
            ]);
    }
}
