<?php namespace Secretstore;

class Keyring {

    /**
     * The name of this keyring.
     * @var string
     */
    private $name;

	/**
	 * The file that contains the keyring.
	 * @var string
	 */
	private $filename;

	public function __construct($filename, $name) {
        $this->filename = $filename;
        $this->name = $name;
	}

	/**
	 * Returns the name of this keyring.
	 *
	 * @return string The name.
	 */
	public function getName() {
	    return $this->name;
	}
}