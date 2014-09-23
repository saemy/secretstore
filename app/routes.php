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
 * Ajax routes.
 */
Route::group(array('before' => 'auth|ajax'), function() {
    Route::get('keyring/{id}/lock', 'KeyringController@getLock');
    Route::post('keyring/{id}/unlock', 'KeyringController@postUnlock');
    Route::get('keyring/{id}/show', 'KeyringController@getShow');
    Route::get('keyring/{id}/secret/{entryId}', 'KeyringController@getSecret');
});

/**
 * Authorized routes.
 */
Route::group(array('before' => 'auth'), function() {
    Route::get('', function() {
        return Redirect::to('keyring');
    });

    Route::controller('keyring', 'KeyringController',
                      array('only' => array('index')));
    Route::get('logout', 'LoginController@getLogout');
});

/**
 * Guest routes
 */
Route::group(array('before' => 'guest'), function() {
    Route::controller('login', 'LoginController');
});
