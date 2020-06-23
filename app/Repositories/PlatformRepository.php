<?php


namespace App\Repositories;


use App\Models\Platform;
use Illuminate\Support\Str;

class PlatformRepository
{
    public static function getAvailable()
    {
        return Platform::all()->reject(function ($platform) {
            return !$platform->available;
        })->values();
    }

    /**
     * Store a new Platform.
     *
     * @param $name
     * @param null $readableName
     * @param bool $available
     * @return Platform
     */
    public static function store($name, $readableName = null, $available = true)
    {
        $platform = new Platform();
        $platform->forceFill([
            'name' => $name,
            'readable_name' => $readableName ? $readableName : Str::kebab($name),
            'available' => $available,
        ]);

        $platform->save();

        return $platform;
    }

    public static function findByName($name)
    {
        return Platform::where('name', $name)->first();
    }

    /**
     * Update the given Platform.
     *
     * @param Platform $platform
     * @param string $name
     * @param $readableName
     * @param $available
     * @return Platform
     */
    public static function update(Platform $platform, $name, $readableName, $available)
    {
        $platform->forceFill([
            'name' => $name,
            'readable_name' => $readableName,
            'available' => $available
        ])->save();

        return $platform;
    }

    /**
     * Delete the given Platform.
     *
     * @param Platform $platform
     * @return void
     * @throws \Exception
     */
    public static function delete(Platform $platform)
    {
        $platform->delete();
    }
}
