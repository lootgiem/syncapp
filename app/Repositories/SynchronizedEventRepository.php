<?php


namespace App\Repositories;


use App\Models\SynchronizedEvent;

class SynchronizedEventRepository
{
    public static function getForUser($userId)
    {
        return SynchronizedEvent::withTrashed()
            ->whereHas('credential', function ($query) use ($userId) {
                $query->where('user_id', '=', $userId);
            })->get();
    }

    public static function getForCredential($credentialId)
    {
        return SynchronizedEvent::withTrashed()
            ->where('credential_id', '=', $credentialId)->get();
    }

    public static function deleteRealIds($realIds)
    {
        $realIds = array_map('strval', $realIds);
        SynchronizedEvent::whereIn('real_id', $realIds)->forceDelete();
    }

    public static function store(string $eventId, int $credentialId, string $realId)
    {
        $synchronizedEvent = new SynchronizedEvent();

        $synchronizedEvent->fill([
            'event_id' => $eventId,
            'credential_id' => $credentialId,
            'real_id' => $realId,
        ]);

        $synchronizedEvent->save();

        return $synchronizedEvent;
    }
}
