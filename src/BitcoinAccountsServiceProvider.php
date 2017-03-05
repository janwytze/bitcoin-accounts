<?php

namespace Jwz104\BitcoinAccounts;

use Illuminate\Support\ServiceProvider;

class BitcoinAccountsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        //Register BitcoinAccount facade
        $this->app->bind('BitcoinAccounts', 'Jwz104\BitcoinAccounts\BitcoinAccounts');

        //Register kernel for scheduled tasks
	$this->app->singleton('jwz104.bitcoinaccounts.console.kernel', function($app) {
	    $dispatcher = $app->make(\Illuminate\Contracts\Events\Dispatcher::class);
	    return new \Jwz104\BitcoinAccounts\Console\Kernel($app, $dispatcher);
	});

	$this->app->make('jwz104.bitcoinaccounts.console.kernel');

        //Publish the config
        $this->publishes([
            __DIR__.'/config' => public_path('config')
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
