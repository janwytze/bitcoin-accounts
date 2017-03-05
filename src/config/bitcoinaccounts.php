<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Connection info
    |--------------------------------------------------------------------------
    |
    | This is all the info to connect to the bitcoind RPC server
    */
    'connection' => [
        'username' => env('BITCOIN_USER'),
        'password' => env('BITCOIN_PASSWORD'),
        'host' => env('BITCOIN_IP'),
        'port' => env('BITCOIN_PORT'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Account settings
    |--------------------------------------------------------------------------
    |
    | Here you can change the account settings
    | 
    | autocreate-address: Create an address when an account is created
    */
    'account' => [
        'autocreate-address' => true,
    ],
];
