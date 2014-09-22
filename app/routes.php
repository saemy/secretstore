<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

/**
 * Authorized routes.
 */
Route::group(array('before' => 'auth'), function() {
    Route::get('', function() {
        return Redirect::to('keyring');
    });

    Route::post('keyring/{id}/unlock', 'KeyringController@postUnlock');
    Route::get('keyring/{id}/secret/{entryId}', 'KeyringController@getSecret');
    Route::controller('keyring', 'KeyringController');
});

/**
 * Guest routes
 */
Route::get('logout', 'LoginController@getLogout');
Route::controller('login', 'LoginController');