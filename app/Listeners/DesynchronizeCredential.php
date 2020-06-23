<?php

namespace App\Listeners;

use App\Events\CredentialToDesynchronize;
use App\Jobs\DesynchronizeJob;
use App\Jobs\SynchronizeJob;
use Closure;

class DesynchronizeCredential
{
    /**
     * Handle the event.
     *
     * @param CredentialToDesynchronize $event
     * @return void
     */
    public function handle(CredentialToDesynchronize $event)
    {
        $chain = [
            new SynchronizeJob($event->credential->user_id)
        ];

        if ($event->closure instanceof Closure) {
            array_push($chain, $event->closure);
        }

        DesynchronizeJob::dispatch($event->credential)->chain($chain);
    }
}
