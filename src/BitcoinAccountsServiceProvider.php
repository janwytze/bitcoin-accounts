<?php

namespace jwz104\Bitcoin;

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
        $this->app->bind('BitcoinAccounts', 'jwz104\Bitcoin\BitcoinAccounts');
        //Register kernel for scheduled tasks
	$this->app->singleton('jwz104.bitcoin.console.kernel', function($app) {
	    $dispatcher = $app->make(\Illuminate\Contracts\Events\Dispatcher::class);
	    return new \jwz104\Bitcoin\Console\Kernel($app, $dispatcher);
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
