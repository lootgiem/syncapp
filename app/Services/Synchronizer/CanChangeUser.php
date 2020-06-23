<?php


namespace App\Services\Synchronizer;


use App\Models\Credential;
use App\Services\Platforms\Platform;

trait CanChangeUser
{
    protected function changePlatformUser(Credential $credential)
    {
        $platform = Platform::resolve($credential);
        $platform->setUser($credential);
        return $platform;
    }
}
