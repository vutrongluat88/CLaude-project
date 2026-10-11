<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bootstrap Admin
    |--------------------------------------------------------------------------
    |
    | The first admin account created by AdminUserSeeder on deploy. Read from
    | config (not env()) so it keeps working after `config:cache`.
    |
    */

    'name' => env('ADMIN_NAME', 'Administrator'),

    'email' => env('ADMIN_EMAIL'),

    'password' => env('ADMIN_PASSWORD'),

];
