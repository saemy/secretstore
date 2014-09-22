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
	 * Unlocks the given keyring and returns it.
	 *
	 * @param string $id
	 * @return Response
	 */
	public function postUnlock($id) {
        $keyring = $this->keyringRepo->find($id);

        $password = Input::get('password');
        $keyring->unlock($password);
        // TODO catch exception on unlock and return unauthorized error code if
        //      invalid password.

        return $this->show($id);
	}

	/**
	 * Displays the specified keyring (must already be unlocked).
	 *
	 * @param  string  $id
	 * @return Response
	 */
	public function show($id) {
		$keyring = $this->keyringRepo->find($id);
		return View::make('keyring.show', compact('keyring'));
	}

	/**
	 * Displays the secret of the specified keyring entry.
	 *
	 * @param string $id
	 * @param string $entryId
	 */
	public function getSecret($id, $entryId) {
		$keyring = $this->keyringRepo->find($id);
		$entry = $keyring->findEntry($entryId);

		return View::make('keyring.secret', compact('entry'));
	}
}
