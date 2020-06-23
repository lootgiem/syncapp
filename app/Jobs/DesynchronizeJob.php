<?php

namespace App\Jobs;

use App\Models\Credential;
use App\Services\Synchronizer\DesynchronizeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DesynchronizeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * @var Credential
     */
    private Credential $credential;

    /**
     * Create a new job instance.
     *
     * @param Credential $credential
     */
    public function __construct(Credential $credential)
    {
        $this->credential = $credential;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->credential->forceFill(['synchronized' => false])->save();
        $desynchronizeService = new DesynchronizeService($this->credential);
        $desynchronizeService->run();
    }
}
