<?php

class L2tp_AVP_Hostname extends L2tp_AVP {

	protected function parse($data) {
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$this->is_mandatory = ($avp_flags_len & 32768) ? true : false;
		$this->is_hidden = ($avp_flags_len & 16384) ? true : false;
		$this->length = ($avp_flags_len & 1023);
		if ($this->length < 1 ) {
			throw new Exception("Invalid length for Hostname AVP!");
		}
		list( , $this->vendor_id) = unpack('n', $data[2].$data[3]);
		list( , $this->type) = unpack('n', $data[4].$data[5]);
		$this->value = substr($data, 6, $this->length - 6);
		$this->validate();
	}

	function setValue($value) {
		// TODO: Possibli we have to check at least length ??
		$this->value = $value;
		return true;
	}

	function encode() {
		throw new Exception("Encode method isn't defined");
	}

	function validate() {
		if ($this->is_hidden) {
			throw new Exception("Hostname AVP must not be HIDDEN");
		}
	}

}
