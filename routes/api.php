<?php

use Illuminate\Support\Facades\Route;

Route::get('/platforms', 'ApiController@platforms')
    ->name('platforms');

Route::group(['middleware' => ['client']], function () {

    Route::get('/credentials', 'ApiController@credentials')
        ->name('credentials');

    Route::get('/desynchronize/{credential}', 'ApiController@desynchronize')
        ->name('desynchronize');

    Route::get('/synchronize', 'ApiController@synchronize')
        ->name('synchronize');
});
