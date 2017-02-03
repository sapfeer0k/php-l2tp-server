<?php

namespace L2tpServer\AVPs;

use L2tpServer\Exceptions\AVPException;

abstract class BaseAVP
{
    protected $isMandatory;
    protected $isHidden;
    protected $value;
    protected $length;
    protected $vendor_id;
    protected $is_ignored;

    public function __construct()
    {
        $this->vendor_id = 0;
    }

    public static function import($data)
    {
        throw new \Exception("You must override method " . (get_class() . '::import()'));
    }

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
//        if ($this->type === null) {
//            throw new AVPException("AVP type must be defined");
//        }
        if ($this->value === null) {
            throw new AVPException("Value is not defined for AVP type {$this->getType()}");
        }
        $flags = 0;
        if ($this->isMandatory) {
            $flags += 32768;
        }
        if ($this->isHidden) {
            throw new \Exception("Implement hidden encoding for AVP");
            //$flags += 16384;
        }
        if ($this->length > pow(2, 10)) {
            throw new \Exception("Length too big");
        }

        $payload = pack('nn', $this->vendor_id, $this->getType()) . $this->getEncodedValue();
        $this->length = 2 + strlen($payload); // first two bytes + all other data
        $flags += $this->length;
        return pack("n", $flags) . $payload;
    }

    abstract protected function getEncodedValue();

    abstract public function setValue($value);

    abstract public function getType();

    abstract protected function validate();

    public function __toString()
    {
        return (new \ReflectionClass($this))->getShortName() . ' ' . json_encode(get_object_vars($this));
    }
}
