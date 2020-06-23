<?php

namespace App\Events;

use App\Models\Credential;
use Closure;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CredentialToDesynchronize
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Credential
     */
    public Credential $credential;

    /**
     * @var Closure
     */
    public ?Closure $closure;

    /**
     * Create a new event instance.
     *
     * @param Credential $credential
     * @param Closure $closure
     */
    public function __construct(Credential $credential, ?Closure $closure = null)
    {
        $this->credential = $credential;
        $this->closure = $closure;
    }
}
