<?php


namespace App\Services\Platforms;


use App\Contracts\Services\Platforms\iPlatform;
use App\Models\Credential;
use App\Models\Platform as PlatformModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Traits\Macroable;

abstract class Platform implements iPlatform
{
    use Macroable;

    public function shouldResetEvents()
    {
        return property_exists($this, 'resetEvents') ? $this->resetEvents : false;
    }

    public function pullEvents()
    {
        $rawEvents = $this->retrieveRawEventsFromPlatform();
        $rawEvents = $this->filterRawEvents($rawEvents);
        return $this->transformToEvents($rawEvents);
    }

    public function pushEvents(Collection $events)
    {
        $rawEvents = $this->transformEventsForPlatform($events);
        return $this->pushEventsToPlatform($rawEvents);
    }

    public function removeEvents(Collection $events)
    {
        $this->removeEventsFromPlatform($events);
    }

    /**
     * @param $parameter
     * @return Platform|ConnectablePlatform|null
     */
    public static function resolve($parameter)
    {
        $platform_id = null;

        if ($parameter instanceof Credential || $parameter instanceof FormRequest) {
            $platform_id = $parameter->platform_id;
        }

        if (is_string($parameter)) {
            $platform_id = $parameter;
        }

        if (!is_null($platform_id)) {
            if ($platform = PlatformModel::find($platform_id)) {
                return app($platform->name);
            }
        }

        abort(500, 'Unresolvable platform instance.');
        return null;
    }

    protected function transformToEvents($rawEvents)
    {
        $events = collect();

        foreach ($rawEvents as $rawEvent) {
            try {
                $events->add($this->transformToEvent($rawEvent));
            } catch (\Exception $e) {
                Log::error('Error: "transformToEvents" -> ' . json_encode($rawEvent) . ' / message : ' . $e->getMessage());
            }
        }

        return $events;
    }

    protected function transformEventsForPlatform(Collection $events)
    {
        $rawEvents = collect();

        foreach ($events as $event) {
            try {
                $rawEvent = $this->transformEventForPlatform($event);
                $rawEvents->put($event->real_id, $rawEvent);
            } catch (\Exception $e) {
                Log::error('Error: "transformEventsForPlatform" -> ' . json_encode($event) . ' / message : ' . $e->getMessage());
            }
        }

        return $rawEvents;
    }

    protected function pushEventsToPlatform(Collection $rawEvents)
    {
        $pushedEventsIds = collect();

        foreach ($rawEvents as $eventRealId => $rawEvent) {
            try {
                $id = $this->pushEventToPlatform($rawEvent);
                $pushedEventsIds->put($eventRealId, $id);
            } catch (\Exception $e) {
                Log::error('Error: "pushEventsToPlatform " -> ' . json_encode($rawEvent) . ' / message : ' . $e->getMessage());
            }
        }

        return $pushedEventsIds;
    }

    protected function removeEventsFromPlatform($rawEvents)
    {
        foreach ($rawEvents as $rawEvent) {
            try {
                $this->removeEventFromPlatform($rawEvent);
            } catch (\Exception $e) {
                Log::error('Error: "removeEventsFromPlatform" -> ' . json_encode($rawEvent) . ' / message : ' . $e->getMessage());
            }
        }
    }

    abstract protected function filterRawEvents($rawEvents);

    abstract protected function retrieveRawEventsFromPlatform();

    abstract protected function transformToEvent($rawEvents);

    abstract protected function transformEventForPlatform($event);

    abstract protected function pushEventToPlatform($rawEvent);

    abstract public function removeEventFromPlatform($rawEvent);
}
