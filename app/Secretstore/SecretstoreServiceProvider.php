<?php namespace Secretstore;

use App;
use Exception;
use Illuminate\Support\ServiceProvider;
use Response;

class SecretstoreServiceProvider extends ServiceProvider {

    /**
     * Registers the error handler.
     */
    public function boot() {
        App::error(function(Exception $exception, $code){
            // Overwrites the code.
            if ($exception->getCode()) {
                $code = $exception->getCode();
            }

            // Any codes which are not specified will be treated as 500.
            if (!in_array($code, array(401, 403, 404, 500))){
                return;
            }

            return Response::make($exception->getMessage(), $code);
        });
    }

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