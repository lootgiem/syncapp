<?php

use App\Services\Platforms\Doctolib;
use App\Services\Platforms\GoogleCalendar;

return [

    'list' => [
        'doctolib' => [
            'class' => Doctolib::class,
            'readable_name' => 'Doctolib',
            'has_agendas' => true,
            'available' => true,
            'login_endpoint' => 'https://m.doctolib.fr/login.json',
            'api_endpoint' => 'https://m.doctolib.fr/api/'
        ],

        'google_calendar' => [
            'class' => GoogleCalendar::class,
            'readable_name' => 'Google Calendar',
            'has_agendas' => true,
            'available' => true,
            'scope' => 'https://www.googleapis.com/auth/calendar',
            'credential_path' => base_path('resources/assets/credentials.json')
        ]
    ]
];
