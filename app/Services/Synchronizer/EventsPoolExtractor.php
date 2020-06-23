<?php


namespace App\Services\Synchronizer;


abstract class EventsPoolExtractor
{
    public function getPlatformEventsToAdd($credentialId, $hardReset = false)
    {
        if ($hardReset) {
            return $this->otherInternalEvents($credentialId);
        }

        return $this->otherInternalEvents($credentialId)
            ->withoutEvents($this->selfUnchangedExternalEvents($credentialId));
    }

    public function getPlatformEventsToDelete($credentialId, $hardReset = false)
    {
        if ($hardReset) {
            return $this->getSynchronizedEventsForCredential($credentialId);
        }

        return $this->selfChangedExternalEvents($credentialId);
    }

    public function getSynchronizedEventsDeleted()
    {
        return $this->deletedExternalEvents();
    }

    public function getDatabaseAddedEvents()
    {
        return $this->newEvents();
    }

    public function getDatabaseDeletedEvents()
    {
        return $this->deletedEvents();
    }

    abstract protected function getSynchronizedEventsForCredential($credentialId);

    abstract protected function otherInternalEvents($credentialId);

    abstract protected function selfUnchangedExternalEvents($credentialId);

    abstract protected function selfChangedExternalEvents($credentialId);

    abstract protected function deletedExternalEvents();

    abstract protected function newEvents();

    abstract protected function deletedEvents();
}
