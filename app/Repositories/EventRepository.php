<?php


namespace App\Repositories;


use App\Models\Event;

class EventRepository
{
    public static function getForUser($userId)
    {
        return Event::withTrashed()
            ->whereHas('credential', function ($query) use ($userId) {
                $query->where('user_id', '=', $userId);
            })->get();
    }

    public static function getForCredential($credentialId)
    {
        return Event::withTrashed()
            ->where('credential_id', '=', $credentialId)->get();
    }

    public static function create($data)
    {
        $event = new Event();
        return $event->fill($data);
    }
}
