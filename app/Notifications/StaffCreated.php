<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffCreated extends Notification implements ShouldQueue
{
    use Queueable;



    public $message;
    public $subject;
    public $fromEmail;
    public $mailer;
    public $otp;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //

        $this->message = 'Welcome to Documents Verification. Your default password is : 1234567 but you are advised to change it to a secure password.Thank you.';
        $this->subject='Welcome onboard';
        $this->fromEmail=env('MAIL_FROM_NAME');
        $this->mailer= env('mailer');

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
        ->mailer(env('MAIL_MAILER'))
        ->subject($this->subject)
        ->greeting('Hello,'.$notifiable->first_name)
        ->line($this->message);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
