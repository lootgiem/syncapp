<?php

namespace App\Providers;

use App\Models\Credential;
use App\Services\GoogleOauthClient;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PlatformServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Route::bind('credential-token', function ($value) {
            return Credential::where('token', Hash::make($value))->firstOrFail();
        });
    }

    public function register()
    {
        $this->registerPlatforms();
        $this->registerServices();
    }

    private function registerPlatforms()
    {
        foreach (config('platforms.list') as $name => $value) {
            $function = new \ReflectionClass($value['class']);
            $this->app->singleton($function->getShortName(), $value['class']);
        }
    }

    protected function registerServices()
    {
        $this->app->bind(Client::class, function () {
            return new Client(['cookies' => true, 'verify' => 'C:\wamp\bin\php\cacert.pem']);
        });
    }
}
