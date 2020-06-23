<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\Credential' => 'App\Policies\CredentialPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot() {
        $this->registerPolicies();

        Passport::routes(function ($router) {
            $router->forAccessTokens();
        });

        app('router')->group(['middleware' => ['web', 'auth'],
                              'prefix' => 'oauth',
                              'namespace' => '\Laravel\Passport\Http\Controllers'], function ($router) {
            $router->get('/clients', [
                'uses' => 'ClientController@forUser',
                'as' => 'passport.clients.index',
            ]);
        });

        Passport::tokensExpireIn(now()->addDays(1));
    }
}
