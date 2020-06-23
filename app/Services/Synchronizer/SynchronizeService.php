<?php


namespace App\Services\Synchronizer;

use Illuminate\Support\Collection;

class SynchronizeService
{
    use CanChangeUser, Reportable;

    protected EventsPool $eventsPool;
    protected Collection $report;
    protected Collection $credentials;

    public function __construct($credentials)
    {
        $this->report = collect();
        $this->credentials = $credentials;
        $this->eventsPool = new EventsPool($credentials->first()->user_id);
    }

    public function run()
    {
        $this->fillEventsPool($this->credentials);
        $platformUpdater = new PlatformUpdater($this->eventsPool);
        $databaseUpdater = new DatabaseUpdater($this->eventsPool);

        [$addedIds, $removedSynchronizedEvents] = $platformUpdater->run($this->credentials);
        $databaseUpdater->run($addedIds, $removedSynchronizedEvents);

        $this->addReportSection('platforms', $platformUpdater->getReport());
        $this->addReportSection('database', $databaseUpdater->getReport());

        return $this->report;
    }

    protected function fillEventsPool($credentials)
    {
        foreach ($credentials as $credential) {
            $platform = $this->changePlatformUser($credential);
            $events = $platform->pullEvents();
            $this->eventsPool->fill($credential, $events);
        }
    }
}
