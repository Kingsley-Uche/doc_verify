<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminEmailVerification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
    // public function toMail($notifiable)
    // {
    //     dd($notifiable);
    //     return (new MailMessage)
    //     ->subject('Admin Email Verification')
    //     ->greeting('Hello! Administrator')
    //     ->line('Please click the button below to verify your email address.')
    //     ->action('Notification Action', url('/verify/email'))
    //     ->line('If you did not create an account, no further action is required.');
    // }
    public function toMail($notifiable)
{
    return (new MailMessage)
        ->subject('Admin Email Verification')
        ->greeting('Hello! Administrator')
        ->line('Please click the button below to verify your email address.')
        ->action('Notification Action', route('verification.verify', [
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ]))
        ->line('If you did not create an account, no further action is required.');
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
