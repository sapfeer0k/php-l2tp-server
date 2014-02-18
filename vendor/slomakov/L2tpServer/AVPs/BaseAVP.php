<?php

namespace L2tpServer\AVPs;

use L2tpServer\Exceptions\AVPException;

abstract class BaseAVP {
	protected $is_mandatory;
	protected $is_hidden;
	protected $value;
	protected $length;
	protected $vendor_id;
	protected $type;
	protected $is_ignored;

	protected abstract function parse($data);

	protected abstract function validate();

	protected abstract function setValue($value);

	protected abstract function encode();

	public function __construct($data=false) {
		$this->is_mandatory = true;
		$this->is_hidden = false;
		$this->is_ignored = false;
        if ($data) {
            $this->import($data);
        }
	}

    public function import($data)
    {
        if($data) {
            if (strlen($data) >= 6) {
                $this->parse($data);
            } else {
                throw new AVPException("AVP length can't be less than 6 bytes!");
            }
        } else {
            throw new AVPException("AVP data must be present!");
        }
    }

	public function isIgnored() {
		return $this->is_ignored;
	}

	public function __get($name) {
		if (method_exists($this, ($method = 'get'.ucfirst($name)))) {
			return $this->$method;
		} else {
			if (property_exists($this, $name)) {
				return $this->$name;
			} else {
				throw new Exception("You're trying to read property '$name' which doesn't exist");
			}
		}
		return NULL;
	}

	public function __set($name, $value) {
		if (method_exists($this, ($method = 'set'.ucfirst($name)))) {
			return $this->$method($value);
		} else {
			if (property_exists($this, $name)) {
				trigger_error("Setter in ".__CLASS__." is unsafe. Pls fix me\n");
				$this->$name = $value;
			} else {
				throw new Exception("You're trying to change property '$name' which doesn't exist");
			}
		}
	}
}
