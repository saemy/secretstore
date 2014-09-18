<?php namespace Secretstore\Repositories;

use Auth;
use File;
use Illuminate\Auth\UserInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Secretstore\Keyring;
use Validator;

class FileKeyringRepository implements KeyringRepositoryInterface {

    /**
     * The user the keyrings should be fetched for.
     * @var UserInterface
     */
    private $user;

    public function __construct() {
        $this->user = Auth::check() ? Auth::user() : null;
    }

    /**
     * Returns
     */
    public function all() {
        $keyrings = array();

        // Reads the keyrings from the users' keyring path.
        if ($this->user) {
            $keyringFiles = File::glob(
                    self::getKeyringStoragePath($this->user) . '*.keyring');
            foreach ($keyringFiles as $keyringFile) {
                $name = self::getKeyringNameFromFilename($keyringFile);
                $keyrings[] = new Keyring($keyringFile, $name);
            }
        }

        return $keyrings;
    }

    public function find($name) {
        $this->requireUser();

        $keyringFile = self::getFilenameFromKeyringName($user, $name);
        if (!File::exists($keyringFile)) {
            throw new ModelNotFoundException;
        }

        return new Keyring($keyringFile);
    }

    public function create($name, $filename) {
        throw Exception('NYI');
    }

    public function validForCreation($name, $filename) {
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

    public function delete($name) {
        $this->requireUser();

        $keyringFile = self::getFilenameFromKeyringName($user, $name);
        $success = File::delete($keyringFile);

        if (!$success) {
            throw new Exception('Could not delete keyring.');
        }
    }

    private function requireUser() {
        if (!$this->user) {
            throw new Exception('No keyring repository user.');
        }
    }

    /**
     * Returns the keyring name from given filename.
     *
     * @param string $filename The filename.
     * @return string The keyring name.
     */
    private static function getKeyringNameFromFilename($filename) {
        $numMatches = preg_match('/\/([^\/]+)\.keyring/', $filename, $matches);
        if ($numMatches != 1) {
            throw new InvalidArgumentException('Invalid keyring filename.');
        }

        return $matches[1];
    }

    /**
     * Returns the keyring storage path for a given keyring name of given user.
     *
     * @param UserInterface $user The user the keyring belongs to.
     * @param string $keyringName The name of the keyring.
     * @return string The filename.
     */
    private static function getFilenameFromKeyringName($user, $keyringName) {
        return sprintf('%s%s.keyring',
                       self::getKeyringStoragePath($user), $keyringName);
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
