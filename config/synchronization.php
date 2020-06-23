<?php

return [
    'remove-past-events' => false,

    'sync_between' => [
        'min' => '6:30',
        'max' => '00:00'
    ],

    'sync_period' => [
        'months' => 3,
    ],

    'compared_properties' => [
        'summary',
        'description',
        'start_date',
        'end_date'
    ],
];
