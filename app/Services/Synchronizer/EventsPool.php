<?php


namespace App\Services\Synchronizer;

use App\Repositories\EventRepository;
use App\Repositories\SynchronizedEventRepository;
use Illuminate\Support\Collection;

class EventsPool extends EventsPoolExtractor
{
    protected int $userId;
    protected Collection $pool;
    protected Collection $userEvents;
    protected Collection $userSynchronizedEvents;

    public function __construct($userId)
    {
        $this->userId = $userId;
        $this->pool = collect();
        $this->userEvents = EventRepository::getForUser($userId);
        $this->userSynchronizedEvents = SynchronizedEventRepository::getForUser($userId);
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function fill($credential, $events)
    {
        $synchronizedEvents = $this->getSynchronizedEventsForCredential($credential->id);
        $eventPool = new EventPool($events, $synchronizedEvents);
        $this->pool->put($credential->id, $eventPool);
    }

    protected function getSynchronizedEventsForCredential($credentialId)
    {
        return $this->userSynchronizedEvents
            ->where('credential_id', '=', $credentialId);
    }

    protected function getEventPool($credentialId)
    {
        return $this->pool->get($credentialId);
    }

    protected function allInternalEvents()
    {
        return $this->pool->pluck('internalEvents')->collapse();
    }

    protected function allExternalEvents()
    {
        return $this->pool->pluck('externalEvents')->collapse();
    }

    protected function otherInternalEvents($credentialId)
    {
        $selfInternalEvents = $this->getEventPool($credentialId)->getInternalEvents();
        return $this->allInternalEvents()->diff($selfInternalEvents);
    }

    protected function deletedExternalEvents()
    {
        return $this->userSynchronizedEvents->withoutEvents($this->allExternalEvents(), ['real_id']);
    }

    protected function selfChangedExternalEvents($credentialId)
    {
        $selfExternalEvents = $this->getEventPool($credentialId)->getExternalEvents();
        return $selfExternalEvents->withoutEvents($this->otherInternalEvents($credentialId));
    }

    protected function selfUnchangedExternalEvents($credentialId)
    {
        $credentialExternalEvents = $this->getEventPool($credentialId)->getExternalEvents();
        return $credentialExternalEvents->diff($this->selfChangedExternalEvents($credentialId));
    }

    protected function newEvents()
    {
        return $this->allInternalEvents()->withoutEvents($this->userEvents);
    }

    protected function deletedEvents()
    {
        return $this->userEvents->withoutEvents($this->unchangedEvents());
    }

    protected function unchangedEvents()
    {
        return $this->allInternalEvents()->withoutEvents($this->newEvents());
    }

}
