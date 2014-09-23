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
}
