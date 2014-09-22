<?php namespace Secretstore\Keyrings\Gnome;

use \Exception;

class Buffer {
    private $buffer;
    private $offset;

    public function __construct($buffer, $offset = 0) {
        $this->buffer = array_values($buffer);
        $this->offset = $offset;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
    }

    public function raw() {
        return $this->buffer;
    }

    private function checkLength($length) {
        if (count($this->buffer) - $this->offset < $length) {
            throw new Exception("No more data in the buffer");
        }
    }

    public function getByte() {
        $this->checkLength(1);
        $ret = $this->buffer[$this->offset];
        ++$this->offset;
        return $ret;
    }

    public function getUint32() {
        $val = $this->getByte() << 24 |
               $this->getByte() << 16 |
               $this->getByte() <<  8 |
               $this->getByte();
        // Enforce unsignedness.
        return (float)sprintf("%u", $val);
    }

    public function getBytes($length) {
        $this->checkLength($length);
        $ret = array_slice($this->buffer, $this->offset, $length);
        $this->offset += $length;
        return $ret;
    }

    public function getByteArray() {
        $len = $this->getUint32();
        if ($len == 0xffffffff) {
            return NULL;
        } else if ($len >= 0x7fffffff) {
            throw new Exception("Array length too big.");
        }

        return $this->getBytes($len);
    }

    public function getBytesStr($length) {
        $bytes = $this->getBytes($length);
        return call_user_func_array("pack", array_merge(array("C*"), $bytes));
    }

    public function getString() {
        $str = $this->getByteArray();
        if ($str == NULL) {
            return "";
        }

        /* Make sure no null characters are in the string */
        if (in_array(0, $str)) {
            throw new Exception("Null char in string.");
        }

        return call_user_func_array("pack", array_merge(array("C*"), $str));
    }

    public function getTime() {
        // array(hi, lo): seconds since 1970-01-01 00:00:00 UTC
        return array($this->getUint32(), $this->getUint32());
    }
}
