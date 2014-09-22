<?php namespace Secretstore;

use \Auth;
use \Crypt;
use \Session;

abstract class Keyring {

    /**
     * The id of this keyring.
     * @var string
     */
    private $id;

    /**
     * The display name of this keyring.
     * @var string
     */
    private $displayName;

	/**
	 * The file that contains the keyring.
	 * @var string
	 */
	private $filename;

	/**
	 * True, if the keyring is unlocked.
	 * @var bool
	 */
	private $unlocked = false;

	/**
	 * The entries of this keyring.
	 * @var KeyringEntry[]
	 */
	private $entries;

	public function __construct($id, $filename) {
        $this->id = $id;
        $this->filename = $filename;

        $this->loadPublic($this->displayName);

        $key = self::getSessionKey($id);
        if (Session::has($key)) {
            try {
                $password = Crypt::decrypt(Session::get($key));
                $this->unlock($password);
            } catch (Exception $e) {
                // Just ignores any errors.
                Session::forget($key);
            }
        }
	}

	/**
	 * Returns the id.
	 *
	 * @return string
	 */
	public function getId() {
	    return $this->id;
	}

	/**
	 * Returns the display name.
	 *
	 * @return string
	 */
	public function getDisplayName() {
	    return $this->displayName;
	}

	/**
	 * Returns the filename of this keyring.
	 *
	 * @return string
	 */
	protected function getFilename() {
	    return $this->filename;
	}


	/**
	 * Returns if the keyring is unlocked.
	 *
	 * @return bool
	 */
	public function isUnlocked() {
        return $this->unlocked;
	}

	/**
	 * Unlocks the keyring.
	 *
	 * @param string password The password to unlock the keyring.
	 */
	public function unlock($password) {
        if (!$this->isUnlocked()) {
            try {
                $this->loadPrivate($password, $this->entries);
                $this->unlocked = true;

                $key = self::getSessionKey($this->id);
                Session::put($key, Crypt::encrypt($password));
            } catch (Excetption $e) {
                App::abort(403, 'Unauthorized action.');
            }
        }
	}

	/**
	 * Returns the entries stored in this keyring.
	 * Requires loadPrivate to be called first.
	 *
	 * @return KeyringEntry[]
	 */
	public function getEntries() {
	    if (!$this->isUnlocked()) {
	        throw new Exception('Access to private data of unlocked keyring.');
	    }

	    return $this->entries;
	}

	/**
	 * Returns the entry with given id.
	 * Requires loadPrivate to be called first.
	 *
	 * @param string $entryId
	 * @return KeyringEntry
	 */
	public function findEntry($entryId) {
	    if (!$this->isUnlocked()) {
	        throw new Exception('Access to private data of unlocked keyring.');
	    }

	    foreach ($this->entries as $entry) {
	        if ($entry->getId() == $entryId) {
	            return $entry;
	        }
	    }

	    throw new Exception(sprintf('Entry with id %s not found.', $entryId));
	}

	/**
	 * Loads the public part of the keyring. It is expected that at least the
	 * display name is public.
	 *
	 * @param string displayName (Out) The display name.
	 */
	protected abstract function loadPublic(&$displayName);

	/**
	 * Loads the private part of the keyring.
	 *
	 * @param string password The password to unlock the private part.
	 * @param KeyringEntry[] entries (Out) The entries that have been loaded.
	 */
	protected abstract function loadPrivate($password, &$entries);


	/**
	 * Returns the session key to persist the password to given keyring.
	 *
	 * @param string $id The id of the keyring
	 */
	private static function getSessionKey($id) {
	    return sprintf("%s|%s", Auth::user()->getAuthIdentifier(), $id);
	}
}