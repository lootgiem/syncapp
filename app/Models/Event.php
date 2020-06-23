<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($event) {
            $event->synchronizedEvents->each(function ($synchronizedEvent) {
                $synchronizedEvent->delete();
            });
        });
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function credential()
    {
        return $this->belongsTo('App\Models\Credential');
    }

    public function synchronizedEvents()
    {
        return $this->hasMany('App\Models\SynchronizedEvent');
    }

    public function toArray()
    {
        return [
            'summary' => $this->summary,
            'location' => $this->location,
            'status' => $this->status,
            'locked' => $this->locked,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ];
    }
}
