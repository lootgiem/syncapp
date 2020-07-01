<?php


namespace App\Contracts\Services\Platforms;


use Illuminate\Support\Collection;

interface iPlatform
{
    /**
     * @param $parameter
     * @return mixed
     */
    public static function resolve($parameter);

    /**
     * @return mixed
     */
    public function shouldResetEvents();

    /**
     * @return Collection
     */
    public function pullEvents();

    /**
     * @param Collection $events
     * @return Collection
     */
    public function pushEvents(Collection $events);

    /**
     * @param Collection $events
     */
    public function removeEvents(Collection $events);
}
