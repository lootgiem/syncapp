<?php


namespace App\Services\Synchronizer;


use App\Models\Credential;
use Illuminate\Support\Collection;

class PlatformUpdater
{
    use CanChangeUser, Reportable;

    protected EventsPool $eventsPool;
    protected Collection $report;

    public function __construct(EventsPool $eventsPool)
    {
        $this->eventsPool = $eventsPool;
        $this->report = collect();
    }

    public function run($credentials)
    {
        $addedIds = collect();
        $removedSynchronizedEvents = collect();

        foreach ($credentials as $credential) {
            [$pushedEventsIds, $eventsToDelete] = $this->updatePlatform($credential);
            $addedIds->put($credential->id, $pushedEventsIds);
            $removedSynchronizedEvents = $removedSynchronizedEvents->merge($eventsToDelete);
        }

        return [$addedIds, $removedSynchronizedEvents];
    }

    protected function updatePlatform(Credential $credential)
    {
        $platform = $this->changePlatformUser($credential);
        $hardReset = $platform->shouldResetEvents();

        [$eventsToAdd, $eventsToDelete] = $this->getPlatformUpdates($credential->id, $hardReset);
        $this->addToReport($credential, $eventsToAdd, $eventsToDelete);

        $platform->removeEvents($eventsToDelete);
        $pushedEventsIds = $platform->pushEvents($eventsToAdd);

        return [$pushedEventsIds, $eventsToDelete];
    }

    protected function getPlatformUpdates($credentialId, $hardReset)
    {
        $eventsToAdd = $this->eventsPool->getPlatformEventsToAdd($credentialId, $hardReset);
        $eventsToDelete = $this->eventsPool->getPlatformEventsToDelete($credentialId, $hardReset);

        return [$eventsToAdd, $eventsToDelete];
    }

    protected function addToReport($credential, $eventsToAdd, $eventsToDelete)
    {
        $this->addReportContent([
            'credential_id' => $credential->id,
            'credential_name' => $credential->name,
            'events_added' => $eventsToAdd->map(function ($item) {
                return $item->toArray();
            }),
            'events_removed' => $eventsToDelete->map(function ($item) {
                return $item->toArray();
            })
        ]);
    }
}
