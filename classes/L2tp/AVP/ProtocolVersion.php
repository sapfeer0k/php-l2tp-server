<?php

class L2tp_AVP_ProtocolVersion extends L2tp_AVP {

	protected function parse($data) {
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$this->is_mandatory = ($avp_flags_len & 32768) ? true : false;
		$this->is_hidden = ($avp_flags_len & 16384) ? true : false;
		$this->length = ($avp_flags_len & 1023);
		if ($this->length != 8 ) {
			throw new Exception("Invalid length for protocol version AVP!");
		}
		list( , $this->vendor_id) = unpack('n', $data[2].$data[3]);
		list( , $this->type) = unpack('n', $data[4].$data[5]);
		$this->value = array() ;
		list( , $this->value["version"]) = unpack('C', $data[6]);
		list( , $this->value["revision"]) = unpack('C', $data[7]);
		$this->validate();
	}

	function setValue($value) {
		 if (is_array($value) && isset($value['version']) && isset($value['revision'])) {
			$this->value = $value;
		 } else {
			 throw new Exception("Invalid value for protocol type AVP");
		 }
		 return true;
	}

	function encode() {
		throw new Exception("Encode method isn't defined");
	}

	function validate() {
		if ($this->is_hidden) {
			throw new Exception("Protocol version AVP must not be HIDDEN");
		}
		if ($this->value['version'] != 1 || $this->value['revision'] != 0) {
			throw new Exception("Protocol version doesn't supported");
		}
#		if (!constants_avp_type::avp_type_exists($this->value)) {
#			if ($this->is_mandatory) {
#				throw new Exception("Invalid messate type AVP. Tunnel must be terminated.");
#			} else {
#				$this->is_ignored = true;
#			}
#		}
	}

}
