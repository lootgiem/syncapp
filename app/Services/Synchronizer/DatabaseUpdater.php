<?php


namespace App\Services\Synchronizer;


use App\Repositories\EventRepository;
use App\Repositories\SynchronizedEventRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class DatabaseUpdater
{
    use Reportable;

    private EventsPool $eventsPool;
    protected Collection $report;

    public function __construct(EventsPool $eventsPool)
    {
        $this->eventsPool = $eventsPool;
        $this->report = collect();
    }

    public function run(Collection $addedIds, Collection $removedSynchronizedEvents)
    {
        $databaseAddedEvents = $this->eventsPool->getDatabaseAddedEvents();
        $databaseDeletedEvents = $this->eventsPool->getDatabaseDeletedEvents();

        $synchronizedEventsDeleted = $this->eventsPool->getSynchronizedEventsDeleted();
        $removedSynchronizedEvents = $removedSynchronizedEvents->merge($synchronizedEventsDeleted);

        $this->addToReport($databaseAddedEvents, $databaseDeletedEvents, $removedSynchronizedEvents);

        $databaseAddedEvents->each(function ($obj) {
            $obj->save();
        });

        $databaseDeletedEvents->each(function ($obj) {
            $obj->forceDelete();
        });

        $this->removeSynchronizedEventsFromDatabase($removedSynchronizedEvents);
        $this->addSynchronizedIdsToDatabase($addedIds);
    }

    protected function removeSynchronizedEventsFromDatabase(Collection $removedSynchronizedEvents)
    {
        $realIds = $removedSynchronizedEvents->pluck('real_id')->all();
        SynchronizedEventRepository::deleteRealIds($realIds);
    }

    protected function addSynchronizedIdsToDatabase($addedIds)
    {
        $events = EventRepository::getForUser($this->eventsPool->getUserId());

        foreach ($addedIds as $credentialId => $pushedEventsIds) {

            foreach ($pushedEventsIds as $eventRealId => $synchronizedEventRealIds) {

                $eventId = $events->where('real_id', $eventRealId)->first()->id;

                foreach (Arr::wrap($synchronizedEventRealIds) as $synchronizedEventRealId) {

                    SynchronizedEventRepository::store($eventId, $credentialId, $synchronizedEventRealId);
                }
            }
        }
    }

    protected function addToReport($eventsToPush, $eventsToRemove, $removedSynchronizedEvents)
    {
        $this->addContent([
            'events_added' => $eventsToPush->map(function ($item) {
                return $item->toArray();
            }),
            'events_removed' => $eventsToRemove->map(function ($item) {
                return $item->toArray();
            }),
            'synchronized_events_removed' => $removedSynchronizedEvents->map(function ($item) {
                return $item->toArray();
            })
        ]);
    }
}
