<?php

namespace L2tpServer\AVPs;

class UnrecognizedAVP extends BaseAVP {

	public function __construct($data) {
		$this->parse($data);
	}

    public function encode() {
        // this avp can't be encoded
    }

    protected function parse($data) {
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$this->is_mandatory = ($avp_flags_len & 32768) ? true : false;
		$this->validate();
	}

	function isIgnored() {
		return $this->is_ignored;
	}

	protected function validate() {
		if ($this->is_mandatory) {
			throw new Exception("Unknown mandatory AVP!");
		} else {
			$this->is_ignored = true;
		}
	}

	protected function setValue($value) {
		// this avp isn't recognized and doesn't have values
		return false;
	}
}
