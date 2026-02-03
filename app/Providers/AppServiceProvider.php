<?php

namespace App\Providers;

use App\Events\TicketCreated;
use App\Listeners\SendTicketCreatedEmail;
use App\Models\Answer;
use App\Models\File;
use App\Models\Ticket;
use App\Models\User;
use App\Observers\AuditObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

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
        Ticket::observe(AuditObserver::class);
        Answer::observe(AuditObserver::class);
        File::observe(AuditObserver::class);
        User::observe(AuditObserver::class);
    }
}
