<?php

namespace App\Jobs;

use App\Repositories\CredentialRepository;
use App\Services\Synchronizer\SynchronizeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SynchronizeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    private int $userId;

    /**
     * Create a new job instance.
     *
     * @param $userId
     */
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $credentials = CredentialRepository::toSynchronizeForUser($this->userId);

        if ($credentials->isNotEmpty()) {
            $synchronizeService = new SynchronizeService($credentials);
            $synchronizeService->run();
        }
    }
}
