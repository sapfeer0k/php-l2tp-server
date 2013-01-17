<?php


class L2tp_AVP_AssignedTunnelId extends L2tp_AVP {

	protected function parse($data) {
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$this->is_mandatory = ($avp_flags_len & 32768) ? true : false;
		$this->is_hidden = ($avp_flags_len & 16384) ? true : false;
		$this->length = ($avp_flags_len & 1023);
		if ($this->length != 8 ) {
			throw new Exception("Invalid length for Assigned Tunnel ID AVP");
		}
		list( , $this->vendor_id) = unpack('n', $data[2].$data[3]);
		list( , $this->type) = unpack('n', $data[4].$data[5]);
		list( , $this->value) = unpack('n', $data[6].$data[7]);
		$this->validate();
	}

	function setValue($value) {
		 if ($value > 0 && $value < 0xFFFF ) {
			$this->value = $value;
		 } else {
			 throw new Exception("Invalid value for Tunnel ID");
		 }
		 return true;
	}

	function encode() {
		$flags = 0;
		if ($this->is_mandatory) {
			$flags+= 128;
		}
		$this->length = 6 + 2; // flags, len, type + value
		$this->validate();
		return pack("CCnnn", $flags, $this->length, 0x01, Constants_AvpType::ASSIGNED_TUNNEL_ID_AVP, $this->value);
	}

	function validate() {
		if (!$this->is_mandatory) {
			throw new Exception_Tunnel("Assigned Tunnel ID should be mandatory AVP");
		}
		if ($this->value == 0) {
			throw new Exception_Tunnel("Assigned Tunnel ID should be greater than 0");
		}
	}
}
