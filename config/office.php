<?php

return [
    'location' => [
        'latitude' => env('OFFICE_LATITUDE', -6.200000),
        'longitude' => env('OFFICE_LONGITUDE', 106.816666),
        'name' => env('OFFICE_NAME', 'Main Office'),
        'address' => env('OFFICE_ADDRESS', 'Jakarta, Indonesia'),
    ],
    // If you have multiple offices
    'locations' => [
        'main' => [
            'latitude' => env('OFFICE_MAIN_LATITUDE', -6.200000),
            'longitude' => env('OFFICE_MAIN_LONGITUDE', 106.816666),
            'name' => 'Main Office',
            'address' => 'Jakarta, Indonesia',
        ],
        'branch' => [
            'latitude' => env('OFFICE_BRANCH_LATITUDE', -6.175110),
            'longitude' => env('OFFICE_BRANCH_LONGITUDE', 106.865036),
            'name' => 'Branch Office',
            'address' => 'Jakarta, Indonesia',
        ],
    ],
];
