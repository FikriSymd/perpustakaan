<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueLoanNotification extends Notification
{
    use Queueable;

    public function __construct(public Loan $loan)
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
        $loan = $this->loan->loadMissing(['book', 'user']);
        $days = $loan->overdueDays();
        $fine = $loan->calculateFine();

        return (new MailMessage)
            ->subject('Pengingat: "'.$loan->book->title.'" terlambat '.$days.' hari')
            ->view('mail.overdue', [
                'name' => $notifiable->name,
                'memberCode' => $notifiable->member_code,
                'bookTitle' => $loan->book->title,
                'bookAuthor' => $loan->book->author,
                'dueAt' => $loan->due_at,
                'daysLate' => $days,
                'fine' => $fine,
            ]);
    }
}
