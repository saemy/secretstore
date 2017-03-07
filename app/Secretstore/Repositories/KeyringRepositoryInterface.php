<?php namespace Secretstore\Repositories;

interface KeyringRepositoryInterface {

    /**
     * Get all keyrings.
     *
     * @return Keyring.
     */
    public function all();

    /**
     * Get a Keyring by its name.
     *
     * @param string $name
     * @return Keyring
     */
    public function find($id);

    /**
     * Create a Keyring.
     *
     * @param string $name
     * @param string $filename
     * @return Keyring
     */
    public function create($name, $filename);

    /**
     * Validate that the given keyring is valid for creation.
     *
     * @param string $name
     * @param string $filename
     * @return \Illuminate\Support\MessageBag
     */
    public function validForCreation($name, $filename);

    /**
     * Delete a keyring.
     *
     * @param string $name
     */
    public function delete($name);

    /**
     * Reloads the logged in user and his keyrings. This is useful if the logged
     * in user changes during the lifetime of this repository.
     */
    public function reloadUser();

    /**
     * Unlocks all keyrings that are protected by the given password.
     *
     * @param $password The password to try unlocking the keyrings with.
     */
    public function unlockAll($password);
}
