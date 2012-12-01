<?php 

class l2tp_bearer_capabilities_avp extends l2tp_avp {

	protected function parse($data) {
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$this->is_mandatory = ($avp_flags_len & 32768) ? true : false;
		$this->is_hidden = ($avp_flags_len & 16384) ? true : false;
		$this->length = ($avp_flags_len & 1023);
		if ($this->length != 10 ) {
			throw new Exception("Invalid length for Bearer Capabilities AVP!");
		}
		list( , $this->vendor_id) = unpack('n', $data[2].$data[3]);
		list( , $this->type) = unpack('n', $data[4].$data[5]);
		$this->value = array();
		list( , $flag_byte) = unpack('C', $data[9]);
		
		$this->value["analog"] = ($flag_byte & 2) ? true : false;
		$this->value["digital"] = ($flag_byte & 1) ? true : false;
		$this->validate();
		print_r($this);
	}

	function setValue($value) {
		// this value is readonly!
		$this->value = array("analog" => 0, "digital" => 0 );
        return true;
	}

	function encode() {
		throw new Exception("Encode method isn't defined");
	}

	function validate() {
#			if (!$this->value['sync']) {
#				throw new Exception("No available Framing Capabilites for this connection!");
#			}
	}

}
