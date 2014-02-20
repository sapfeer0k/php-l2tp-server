<?php

namespace L2tpServer\AVPs;

use L2tpServer\Exceptions\AVPException;


abstract class BaseAVP
{
    protected $is_mandatory;
    protected $is_hidden;
    protected $value;
    protected $length;
    protected $vendor_id;
    protected $type;
    protected $is_ignored;

    public function __construct()
    {
        if ($this->type === NULL) {
            throw new AVPException("Type must be defined in every AVP");
        }
        $this->vendor_id = 0;
    }

    public abstract static function import($data);

    public function isIgnored()
    {
        return $this->is_ignored;
    }

    public function __get($name)
    {
        if (method_exists($this, ($method = 'get' . ucfirst($name)))) {
            return $this->$method;
        }
        if (!property_exists($this, $name)) {
            throw new AVPException("You're trying to read property '$name' which doesn't exist");
        }
        return $this->$name;
    }

    public function encode()
    {
        if ($this->type === null) {
            throw new AVPException("AVP type must be defined");
        }
        if ($this->value === NULL) {
            throw new AVPException("Value is not defined for AVP type {$this->type}");
        }
        $flags = 0;
        if ($this->is_mandatory) {
            $flags += 32768;
        }
        if ($this->is_hidden) {
            throw new \Exception("Implement hidden encoding for AVP");
            $flags += 16384;
        }
        if ($this->length > pow(2, 10)) {
            throw new \Exception("Length too big");
        }

        $payload = pack('nn', $this->vendor_id, $this->type) .  $this->getEncodedValue();
        $this->length = 2 + mb_strlen($payload); // first two bytes + all other data
        $flags += $this->length;
        return pack("n", $flags) . $payload;
    }

    public abstract function setValue($value);

    protected abstract function getEncodedValue();

    protected abstract function validate();

}
