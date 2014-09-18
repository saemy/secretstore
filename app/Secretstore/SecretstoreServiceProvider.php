<?php namespace Secretstore;

use Illuminate\Support\ServiceProvider;

class SecretstoreServiceProvider extends ServiceProvider {

    /**
     * Register the service providers.
     *
     * @return void
     */
    public function register() {
    	$this->app->singleton(
    	    'Secretstore\Repositories\KeyringRepositoryInterface',
            'Secretstore\Repositories\FileKeyringRepository');
    }

}