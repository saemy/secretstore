<?php

use Secretstore\Keyring;
use Secretstore\Repositories\KeyringRepositoryInterface;

class KeyringController extends \BaseController {

    private $keyringRepo;

    public function __construct(KeyringRepositoryInterface $keyringRepo) {
        $this->keyringRepo = $keyringRepo;
    }

	/**
	 * Display a listing of the keyrings.
	 *
	 * @return Response
	 */
	public function getIndex() {
	    $keyrings = $this->keyringRepo->all();
		return View::make('keyring.list', compact('keyrings'));
	}


	/**
	 * Display the specified keyring.
	 *
	 * @param  string  $id
	 * @return Response
	 */
	public function show($id) {
		//
	}

}
