<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            $code = explode('/', $url);
            $code = $code[count($code) - 1];
            return (new MailMessage)
                ->subject('Email address verification code')
                ->line('Please copy the code above and paste it into the field designated for the verification code in the application or website.')
                ->greeting($code);
        });
    }
}
