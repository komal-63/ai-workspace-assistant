<?php

namespace App\Providers;

use App\Events\DocumentProcessed;
use App\Listeners\SendDocumentProcessedNotification;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        Event::listen(
            DocumentProcessed::class,
            SendDocumentProcessedNotification::class
        );
    }
}
