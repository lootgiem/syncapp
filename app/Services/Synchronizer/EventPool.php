<?php


namespace App\Services\Synchronizer;


use Illuminate\Support\Collection;

class EventPool
{
    public Collection $internalEvents;
    public Collection $externalEvents;

    public function __construct($events, $synchronizedEvents)
    {
        $this->splitInternalExternalEvents($events, $synchronizedEvents);
    }

    protected function splitInternalExternalEvents($events, $synchronizedEvents)
    {
        $this->internalEvents = $events->withoutEvents($synchronizedEvents, ['real_id']);
        $this->externalEvents = $events->diff($this->internalEvents);
    }

    public function getInternalEvents()
    {
        return $this->internalEvents;
    }

    public function getExternalEvents()
    {
        return $this->externalEvents;
    }
}
