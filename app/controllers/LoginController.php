<?php

class LoginController extends BaseController {

    /**
     * Create a new login controller instance.
     *
     * @return LoginController
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get the user login view.
     */
    public function getIndex() {
        return View::make('login');
    }

    /**
     * Handle a user login attempt.
     */
    public function postIndex() {
        $username = mb_strtolower(Input::get('username'));
        $password = Input::get('password');

        if (Auth::attempt(array('username' => $username, 'password' => $password))) {
        	return Redirect::intended('');
        }

        return Redirect::back()
            ->withInput(Input::except('password'))
            ->with('login_errors', true);
    }

    /**
     * Log out the user
     */
    public function getLogout() {
        Auth::logout();
        return Redirect::to('login');
    }

}

