<?php


class l2tp_message_type extends l2tp_avp {

	function __construct($data=false) {
		$this->is_mandatory = false;
		$this->is_hidden = false;
		$this->is_valid = true;
		$this->is_ignored = false;

		if($data) {
			if (strlen($data) >= 6) {
				$this->parse($data);
			} else {
				throw new Exception("AVP length can't be less than 6 bytes!");
			}
		} else {
		}
	}

	protected function parse($data) {
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$this->is_mandatory = ($avp_flags_len & 32768) ? true : false;
		$this->is_hidden = ($avp_flags_len & 16384) ? true : false;
		$this->length = ($avp_flags_len & 1023);
		if ($this->length != 8 ) {
			$this->is_valid = false;
			throw new Exception("Invalid length for message type!");
		}
		list( , $this->vendor_id) = unpack('n', $data[2].$data[3]);
		list( , $this->type) = unpack('n', $data[4].$data[5]);
		list( , $this->value) = unpack('n', $data[6].$data[7]);
		$this->validate();
	}

	function setValue($value) {
		 if ($value > 0 && $value < 65536 ) {
			$this->value = $value;
		 } else {
			 throw new Exception("Invalid value");
		 }
		 return true;
	}

	function encode() {
		throw new Exception("Encode method isn't defined");
	}

	function validate() {
		if(!property_exists(constants_avp_type , $this->value) && $this->is_mandatory ) {
			$this->is_valid = false;
			throw new Exception("Invalid messate type AVP");
		} else {
			$this->is_ignored = true;
		}
	}

}
