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

    /*
    |--------------------------------------------------------------------------
    | Bitcoin settings
    |--------------------------------------------------------------------------
    |
    | Here you can change the bitcoin settings
    |
    | fee: The fee per transaction
    | confirmations: The amount of confirmations before the bitcoins get added
    */
    'bitcoin' => [
        'transaction-fee' => '0.0001',
        'confirmations' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cronjob settings
    |--------------------------------------------------------------------------
    |
    | Here you can change the cronjob
    |
    | load: The cron settings for the transaction loading
    | send: The cron settings for the transaction sending
    */
    'cronjob' => [
        'load' => '* * * * *',
        'send' => '*/10 * * * *',
    ]
];
