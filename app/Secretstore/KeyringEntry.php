<?php namespace Secretstore;

class KeyringEntry {

    /**
     * The id of this entry.
     * @var string
     */
    private $id;

    /**
     * The to be displayed name of this entry.
     * @var string
     */
    private $displayName;

    /**
     * The secret.
     * @var string
     */
    private $secret;

    public function __construct($id) {
        $this->id = $id;
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
     * Returns the secret.
     *
     * @return string
     */
    public function getSecret() {
        return $this->secret;
    }

    /**
     * Sets the display name.
     * @param string $displayName
     */
    public function setDisplayName($displayName) {
        $this->displayName = $displayName;
    }

    /**
     * Sets the secret.
     * @param string $secret
     */
    public function setSecret($secret) {
        $this->secret = $secret;
    }
}