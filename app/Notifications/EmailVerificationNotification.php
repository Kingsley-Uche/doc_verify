<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Otp;

class EmailVerificationNotification extends Notification
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
        $this->message = 'Use the verification code below to login';
        $this->subject='verification needed';
        $this->fromEmail=env('MAIL_FROM_NAME');
        $this->mailer= env('mailer');
        $this->otp = new Otp;

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
        $otp =$this->otp->generate($notifiable->email,6,5);
        return (new MailMessage)
            ->mailer(env('MAIL_MAILER'))
            ->subject($this->subject)
            ->greeting('Hello,'.$notifiable->first_name)
            ->line($this->message)
            ->line('code :'.$otp->token);
                    // ->line('The introduction to the notification.')
                    // ->action('Notification Action', url('/'))
                    // ->line('Thank you for using our application!');
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
