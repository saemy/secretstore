<?php

use Secretstore\Keyring;
use Secretstore\Repositories\KeyringRepositoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

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
	    try {
            $keyring = $this->keyringRepo->find($id);

            $password = Input::get('password');
            $keyring->unlock($password);
            return $this->getShow($id);
	    } catch (BadCredentialsException $exception) {
            return Response::make(
                    Lang::get('secretstore.keyring_invalid_password'), 403);
	    }
	}

	/**
	 * Displays the specified keyring (must already be unlocked).
	 *
	 * @param  string  $id
	 * @return Response
	 */
	public function getShow($id) {
        $keyring = $this->keyringRepo->find($id);
        $keyring->ensureUnlocked();
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
