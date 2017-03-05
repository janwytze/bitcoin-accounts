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
        $this->app->bind('BitcoinAccounts', 'Jwz104\Bitcoin\BitcoinAccounts');
        //Register kernel for scheduled tasks
	$this->app->singleton('jwz104.bitcoin.console.kernel', function($app) {
	    $dispatcher = $app->make(\Illuminate\Contracts\Events\Dispatcher::class);
	    return new \Jwz104\BitcoinAccounts\Console\Kernel($app, $dispatcher);
	});

	$this->app->make('jwz104.bitcoin.console.kernel');
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
