<?php

namespace App\Listeners;

use App\Events\AnswerCreated;
use App\Mail\AnswerCreatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendAnswerCreatedEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AnswerCreated $event): void
    {
        Mail::to('admin@soporte.com')->send(new AnswerCreatedMail($event->answer));
    }
}
