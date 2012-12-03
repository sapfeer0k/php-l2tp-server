<?php


class l2tp_firmware_revision_avp extends l2tp_avp {

	protected function parse($data) {
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$this->is_mandatory = ($avp_flags_len & 32768) ? true : false;
		$this->is_hidden = ($avp_flags_len & 16384) ? true : false;
		$this->length = ($avp_flags_len & 1023);
		if ($this->length != 8 ) {
			throw new Exception("Invalid length for Firmware Revision");
		}
		list( , $this->vendor_id) = unpack('n', $data[2].$data[3]);
		list( , $this->type) = unpack('n', $data[4].$data[5]);
		list( , $this->value) = unpack('n', $data[6].$data[7]);
		$this->validate();
	}

	function setValue($value) {
		if ($value >= 0 && $value < 65535) {
			$this->value = $value;
		} else {
			throw new Exception("Invalid value for Firmware Revision");
		}
		return true;
	}

	function encode() {
		throw new Exception("Encode method isn't defined");
	}

	function validate() {
		if ($this->is_mandatory) {
			throw new Exception("Firmware Revision should not be mandatory!");
		}
	}
}
