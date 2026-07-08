<?php

namespace App\Providers;

use App\Mail\Transport\MailjetTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Password::defaults(fn () => Password::min(8)->mixedCase()->numbers());

        Mail::extend('mailjet', fn (array $config) => new MailjetTransport($config['key'], $config['secret']));
    }
}
