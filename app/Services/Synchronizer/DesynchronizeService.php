<?php


namespace App\Services\Synchronizer;


use App\Models\Credential;
use App\Repositories\EventRepository;
use App\Repositories\SynchronizedEventRepository;
use Illuminate\Support\Collection;

class DesynchronizeService
{
    use CanChangeUser, Reportable;

    private Credential $credential;
    protected Collection $report;

    public function __construct(Credential $credential)
    {
        $this->credential = $credential;
        $this->report = collect();
    }

    public function run()
    {
        $events = EventRepository::getForCredential($this->credential->id);
        $synchronizedEvents = SynchronizedEventRepository::getForCredential($this->credential->id);

        $platform = $this->changePlatformUser($this->credential);
        $platform->removeEvents($synchronizedEvents);

        $events->each(function ($obj) {
            $obj->delete();
        });

        $synchronizedEvents->each(function ($obj) {
            $obj->forceDelete();
        });

        $this->addReportSection('platforms', [
            'events_removed' => $synchronizedEvents->toArray()
        ]);

        $this->addReportSection('database', [
            'events_removed' => $events->toArray(),
            'synchronized_events_removed' => $synchronizedEvents->toArray(),
        ]);

        return $this->report;
    }
}
