<?php

use Illuminate\Support\Facades\Route;

Auth::routes(['verify' => true]);

Route::get('/', function () {
    return redirect()->route('login');
});

$this->router->group(['middleware' => ['auth', 'verified']], function ($router) {

    $router->get('/profil', 'ProfilController')
        ->name('profil');

    Route::resource('credential', 'CredentialController')
        ->except(['create', 'edit', 'show']);

    $router->get('/credential/agendas/{credential}', 'CredentialController@agendas')
        ->name('credential.agendas');

    $this->router->group(['prefix' => 'connector', 'namespace' => 'Connectors'], function ($router) {

        $router->get('/oauth/callback', 'OauthController@callback')
            ->middleware('CheckStateQueryString')
            ->name('oauth.callback');

        $router->get('/oauth/{credential}', 'OauthController@redirectToAuth')
            ->name('oauth.redirect-to-auth');
    });
});





