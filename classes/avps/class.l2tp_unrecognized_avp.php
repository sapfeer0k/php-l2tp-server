<?php


class l2tp_unrecognized_avp extends l2tp_avp {

	function __construct($data) {
		$this->is_valid = false;
		$this->parse($data);
	}

	protected function parse($data) {
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$this->is_mandatory = ($avp_flags_len & 32768) ? true : false;
		$this->validate();
	}

	function isIgnored() {
		return $this->is_ignored;
	}

	function isValid() {
		return $this->is_valid;
	}

	protected function validate() {
		if (!$this->is_valid) {
			if ($this->is_mandatory) {
				throw new Exception("");
			} else {
				$this->is_ignored = true;
			}
		}
	}

	protected function setValue($value) {
		// this avp isn't recognized and doesn't have values
		return false;
	}

	protected function encode() {
		// this avp can't be encoded
	}
}
