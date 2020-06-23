<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Return the platform service bind of the platform name.
 * @method static resolve($request)
 */
class Platform extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    public function credentials() {
        return $this->hasMany('App\Models\Credential');
    }

    public function events() {
        return $this->hasMany('App\Models\Event');
    }

    public function synchronizedEvents()
    {
        return $this->hasMany('App\Models\SynchronizedEvent');
    }
}
