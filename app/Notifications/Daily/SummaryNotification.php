<?php

declare(strict_types=1);

namespace App\Notifications\Daily;

use App\Forecast;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SummaryNotification extends Notification
{
    use Queueable;

    public function __construct(
        // weather forecast near a user
        // article headlines
        // Transactions from a period,
        // domain expirations
        public ?array $articles,
        public ?Forecast $forecast,
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage);
        $message->view(
            'emails.daily.summary',
            [
                'articles' => $this->articles,
                'weather' => $this->forecast,
            ]
        );

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return (array) $this;
    }
}
