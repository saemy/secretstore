<?php namespace Secretstore\Keyrings\Gnome;

use \Exception;

class GnomeKeyring extends \Secretstore\Keyring {

    private $ctime;
    private $mtime;
    private $flags;
    private $lockTimeout;
    private $hashIterations;
    private $salt;
    private $privateData;

    public function __construct($id, $filename) {
        parent::__construct($id, $filename);
    }

    protected function loadPublic(&$displayName) {
        $data = file_get_contents($this->getFilename());
        $data = unpack("C*", $data);
        $buffer = new Buffer($data);

        // Checks the header of the file.
        $KEYRING_FILE_HEADER = "GnomeKeyring\n\r\0\n";
        $header = $buffer->getBytesStr(strlen($KEYRING_FILE_HEADER));
        if (strcmp($header, $KEYRING_FILE_HEADER) != 0) {
            throw new Exception("Invalid header.");
        }

        $major = $buffer->getByte();
        $minor = $buffer->getByte();
        $crypto = $buffer->getByte();
        $hash = $buffer->getByte();

        if ($major != 0 || $minor != 0 || $crypto != 0 || $hash != 0) {
            throw new Exception("Invalid version.");
        }

        $displayName = $buffer->getString();
        $this->ctime = $buffer->getTime();
        $this->mtime = $buffer->getTime();
        $this->flags = $buffer->getUint32();
        $this->lockTimeout = $buffer->getUint32();
        $this->hashIterations = $buffer->getUint32();
        $this->salt = $buffer->getBytesStr(8);

        // Ignores reserved data.
        for ($i = 0; $i < 4; ++$i) {
            $reserved = $buffer->getUint32();
            if ($reserved != 0) {
                throw new Exception("Invalid reserved bytes.");
            }
        }

        $numItems = $buffer->getUint32();
        if ($numItems > 0) {
            /* Hashed data, without secrets */
            $this->items = self::readHashedItemInfo($buffer, $numItems);
        } else {
            $this->items = array();
        }

        /* Make sure the crypted part is the right size */
        $cryptoSize = $buffer->getUint32();
        if ($cryptoSize % 16 != 0) {
            throw new Exception("Invalid crypto size.");
        }

        $this->privateData = $buffer->getBytesStr($cryptoSize);
        $this->publicLoaded = true;
    }

    protected function loadPrivate($password, &$entries) {
        $privateData = self::decryptPrivateData($password, $this->salt,
                                                $this->hashIterations,
                                                $this->privateData);

        $this->verifyDecryptedData($privateData);
        $buffer = new Buffer(unpack("C*", $privateData));
        $buffer->setOffset(16); /* Skip hash */

        $this->readFullItemInfo($buffer, $this->items);

        $entries = $this->items;
    }

    private static function readHashedItemInfo(&$buffer, $numItems) {
        $items = array();
        for ($i = 0; $i < $numItems; ++$i) {
            $id = $buffer->getUint32();
            $item = new KeyringEntry($id);
            $item->type = $buffer->getUint32();
            $item->attributes = self::readItemAttributes($buffer, true);

            $items[$i] = $item;
        }

        return $items;
    }

    private static function readFullItemInfo(&$buffer, &$items) {
        foreach ($items as $item) {
            $item->setDisplayName($buffer->getString());
            $item->setSecret($buffer->getString());
            $item->ctime = $buffer->getTime();
            $item->mtime = $buffer->getTime();

            // Ignores reserved data.
            $buffer->getString();
            for ($i = 0; $i < 4; ++$i) {
                $buffer->getUint32();
            }

            $item->attributes = self::readItemAttributes($buffer, false);
            $item->acl = self::decodeAcl($buffer);
        }

        return TRUE;
    }

    private static function readItemAttributes(&$buffer, $hashed) {
        $listSize = $buffer->getUint32();

        $attributes = array();
        for ($i = 0; $i < $listSize; ++$i) {
            $name = $buffer->getString();

            $type = $buffer->getUint32();
            switch ($type) {
                case 0: /* A string */
                    $val = $buffer->getString();
                    break;
                case 1: /* A uint32 */
                    $val = $buffer->getUint32();
                    break;

                default:
                    throw new Exception("Unknown attribute type.");
            }

            $attributes[$name] = $val;
        }

        return $attributes;
    }

    private static function decodeAcl(&$buffer) {
        $acl = array();

        $numAce = $buffer->getUint32();

        for ($i = 0; $i < $numAce; ++$i) {
            $ace = new ACEntry();
            $ace->typesAllowed = $buffer->getUint32();
            $ace->name = $buffer->getString();
            $ace->path = $buffer->getString();

            $reserved = $buffer->getString();
            $reserved2 = $buffer->getUint32();

            $acl[$i] = $ace;
        }

        return $acl;
    }

    // privateData has to be a string.
    private static function decryptPrivateData($password, $salt,
                                               $hashIterations, $privateData) {
        $cipher = MCRYPT_RIJNDAEL_128; // AES128
        $mode = MCRYPT_MODE_CBC;

        list($key, $iv) = self::symkeyGenerateSimple(
            16,//mcrypt_get_key_size($cipher, $mode), // gives 32 due to a bug.
            mcrypt_get_iv_size($cipher, $mode),
            $password, $salt, $hashIterations);

        return mcrypt_decrypt($cipher, $key, $privateData, $mode, $iv);
    }

    private static function symkeyGenerateSimple($keySize, $ivSize, $password,
                                                 $salt, $hashIterations) {
        $hashFunction = "sha256";

        $key = "";
        $iv = "";

        for ($pass = 0; strlen($key) < $keySize || strlen($iv) < $ivSize;
             ++$pass) {
            $mdh = hash_init($hashFunction);

            /* Hash in the previous buffer on later passes */
            if ($pass > 0) {
                hash_update($mdh, $digest);
            }

            if (!empty($password)) {
                hash_update($mdh, $password);
            }

            if (!empty($salt)) {
                hash_update($mdh, $salt);
            }

            $digest = hash_final($mdh, true);
            for ($i = 1; $i < $hashIterations; ++$i) {
                $digest = hash($hashFunction, $digest, true);
            }

            /* Copy as much as possible into the key & iv */
            $copyKey = min($keySize - strlen($key), strlen($digest));
            if ($copyKey < 0)  $copyKey = 0;
            $key .= substr($digest, 0, $copyKey);

            $copyIv = min($ivSize - strlen($iv), strlen($digest) - $copyKey);
            if ($copyIv < 0) $copyIv = 0;
            $iv .= substr($digest, $copyKey, $copyIv);
        }

        return array($key, $iv);
    }

    private static function verifyDecryptedData($data) {
        $expectedHash = substr($data, 0, 16);
        $realHash = hash("md5", substr($data, 16), true);

        if (strcmp($expectedHash, $realHash) != 0) {
            throw new Exception("Hash comparision failed.");
        }
    }

}
