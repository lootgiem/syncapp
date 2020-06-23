<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SynchronizedEvent extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function event()
    {
        return $this->belongsTo('App\Models\Event')->withTrashed();
    }

    public function credential()
    {
        return $this->belongsTo('App\Models\Credential');
    }

    public function toArray()
    {
        return $this->event->toArray();
    }
}
