<?php namespace Secretstore\Repositories;

use Auth;
use File;
use Illuminate\Auth\UserInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Secretstore\Keyrings\Gnome\GnomeKeyring;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Validator;

class FileKeyringRepository implements KeyringRepositoryInterface {

    /**
     * The user the keyrings should be fetched for.
     * @var UserInterface
     */
    private $user;

    /**
     * Every keyring is only instantiated once. This map caches them.
     * @var Keyring[]
     */
    private $keyringCache;

    public function __construct() {
        $this->user = Auth::check() ? Auth::user() : null;
        $this->keyringCache = array();
    }

    public function reloadUser() {
        $newUser = Auth::check() ? Auth::user() : null;
        if ($newUser != $this->user) {
            $this->user = $newUser;

            foreach ($this->keyringCache as $keyring) {
                $keyring->lock();
            }
            $this->keyringCache = array();
        }
    }

    public function all() {
        $keyrings = array();

        // Reads the keyrings from the users' keyring path.
        if ($this->user) {
            $keyringFiles = File::glob(
                    self::getKeyringStoragePath($this->user) . '*.keyring');
            foreach ($keyringFiles as $keyringFile) {
                $id = self::getKeyringIdFromFilename($keyringFile);
                $keyring = isset($this->keyringCache[$id])
                    ? $this->keyringCache[$id]
                    : new GnomeKeyring($id, $keyringFile);

                $this->keyringCache[$id] = $keyring;
                $keyrings[] = $keyring;
            }
        }

        return $keyrings;
    }

    public function find($id) {
        $this->requireUser();

        if (isset($this->keyringCache[$id])) {
            $keyring = $this->keyringCache[$id];
        } else {
            $keyringFile = self::getFilenameFromKeyringId($this->user, $id);
            if (!File::exists($keyringFile)) {
                throw new ModelNotFoundException;
            }

            $keyring = new GnomeKeyring($id, $keyringFile);
            $this->keyringCache[$id] = $keyring;
        }

        return $keyring;
    }

    public function create($id, $filename) {
        throw Exception('NYI');
    }

    public function validForCreation($id, $filename) {
        throw Exception('NYI');
//         $rules = array(
//             'name' => 'required|unique',
//             'filename' => 'required'
//         );
//
//         with($validator = Validator::make(compact('filename'), $rules))->fails();
//
//         return $validator->errors();
    }

    public function delete($id) {
        $this->requireUser();

        $keyringFile = self::getFilenameFromKeyringId($user, $id);
        $success = File::delete($keyringFile);

        if (!$success) {
            throw new Exception('Could not delete keyring.');
        }
    }

    public function unlockAll($password) {
        $keyrings = $this->all();
        foreach ($keyrings as $keyring) {
            try {
                $keyring->unlock($password);
            } catch (BadCredentialsException $exception) {
                // Just ignore the error.
            }
        }
    }

    private function requireUser() {
        if (!$this->user) {
            throw new Exception('No keyring repository user.');
        }
    }

    /**
     * Returns the keyring id from given filename.
     *
     * @param string $filename The filename.
     * @return string The keyring id.
     */
    private static function getKeyringIdFromFilename($filename) {
        $numMatches = preg_match('/\/([^\/]+)\.keyring/', $filename, $matches);
        if ($numMatches != 1) {
            throw new InvalidArgumentException('Invalid keyring filename.');
        }

        return $matches[1];
    }

    /**
     * Returns the keyring storage path for a given keyring id of given user.
     *
     * @param UserInterface $user The user the keyring belongs to.
     * @param string $keyringId The id of the keyring.
     * @return string The filename.
     */
    private static function getFilenameFromKeyringId($user, $keyringId) {
        return sprintf('%s%s.keyring',
                       self::getKeyringStoragePath($user), $keyringId);
    }

    /**
     * Returns the given users' keyring storage path.
     * @param UserInterface $user The user.
     * @return string The storage path.
     */
    private static function getKeyringStoragePath($user) {
        return sprintf('%s/keyrings/%s/',
                       storage_path(), $user->getAuthIdentifier());
    }
}
