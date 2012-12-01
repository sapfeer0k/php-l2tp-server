<?php

define('MESSAGE_TYPE_AVP', 0);
define('RESULT_CODE_AVP', 1);
define('PROTOCOL_VERSION_AVP', 2);
define('FRAMING_CAPABILITIES_AVP', 3);
define('BEARER_CAPABILITIES_AVP', 4);
define('TIE_BREAKER_AVP', 5);
define('FIRMWARE_REVISION_AVP', 6);
define('HOSTNAME_AVP', 7);
define('VENDOR_NAME_AVP', 8);
define('ASSIGNED_TUNNEL_ID_AVP', 9);
define('RECEIVE_WINDOW_SIZE_AVP', 10);
define('CHALLENGE_AVP', 11);
define('CAUSE_CODE_AVP', 12);
define('RESPONSE_AVP', 13);
define('ASSIGNED_SESSION_ID_AVP', 14);
define('CALL_SERIAL_NUMBER_AVP', 15);
define('MINIMUM_BPS_AVP', 16);
define('MAXIMUM_BPS_AVP', 17);
define('BEARER_TYPE_AVP', 18);
define('FRAMING_TYPE_AVP', 19);
define('CALLED_NUMBER_AVP', 21);
define('CALLING_NUMBER_AVP', 22);
define('SUBADDRESS_AVP', 23);
define('TX_CONNECTION_SPEED_BPS_AVP', 24);
define('PHYSICAL_CHANNEL_ID_AVP', 25);
define('INITIAL_RECEIVED_LCP_CONFREQ_AVP', 26);
define('LAST_SENT_LCP_CONFREQ_AVP', 27);
define('LAST_RECEIVED_LCP_CONFREQ_AVP', 28);
define('PROXY_AUTHEN_TYPE_AVP', 29);
define('PROXY_AUTHEN_NAME_AVP', 30);
define('PROXY_AUTHEN_CHALLENGE_AVP', 31);
define('PROXY_AUTHEN_ID_AVP', 32);
define('PROXY_AUTHEN_RESPONSE_AVP', 33);
define('CALL_ERRORS_AVP', 34);
define('ACCM_AVP', 35);
define('RANDOM_VECTOR_AVP', 36);
define('PRIVATE_GROUP_ID_AVP', 37);
define('RX_CONNECTION_SPEED_BPS_AVP', 38);
define('SEQUENCE_REQUIRED_AVP', 39);



$known_message_types = array ( MESSAGE_TYPE_AVP, 
 RESULT_CODE_AVP, 
 PROTOCOL_VERSION_AVP, 
 FRAMING_CAPABILITIES_AVP, 
 BEARER_CAPABILITIES_AVP, 
 TIE_BREAKER_AVP, 
 FIRMWARE_REVISION_AVP, 
 HOSTNAME_AVP, 
 VENDOR_NAME_AVP, 
 ASSIGNED_TUNNEL_ID_AVP, 
 RECEIVE_WINDOW_SIZE_AVP, 
 CHALLENGE_AVP, 
 CAUSE_CODE_AVP, 
 RESPONSE_AVP, 
 ASSIGNED_SESSION_ID_AVP, 
 CALL_SERIAL_NUMBER_AVP, 
 MINIMUM_BPS_AVP, 
 MAXIMUM_BPS_AVP, 
 BEARER_TYPE_AVP, 
 FRAMING_TYPE_AVP, 
 CALLED_NUMBER_AVP, 
 CALLING_NUMBER_AVP, 
 SUBADDRESS_AVP, 
 TX_CONNECTION_SPEED_BPS_AVP, 
 PHYSICAL_CHANNEL_ID_AVP, 
 INITIAL_RECEIVED_LCP_CONFREQ_AVP, 
 LAST_SENT_LCP_CONFREQ_AVP, 
 LAST_RECEIVED_LCP_CONFREQ_AVP, 
 PROXY_AUTHEN_TYPE_AVP, 
 PROXY_AUTHEN_NAME_AVP, 
 PROXY_AUTHEN_CHALLENGE_AVP, 
 PROXY_AUTHEN_ID_AVP, 
 PROXY_AUTHEN_RESPONSE_AVP, 
 CALL_ERRORS_AVP, 
 ACCM_AVP, 
 RANDOM_VECTOR_AVP, 
 PRIVATE_GROUP_ID_AVP, 
 RX_CONNECTION_SPEED_BPS_AVP, 
 SEQUENCE_REQUIRED_AVP, 
};


class l2tp_avp {

	function __construct($data=false) {
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

	function validate() {
		if(!in_array($this->value, $known_message_types) && $this->is_mandatory ) {
			$this->is_valid = false;
		} else {
			$this->is_ignored = true;
		}
	}

	function isValid() {
		return $this->is_valid;
	}

	private function fromBinary() {

	}

	function toBinary($data) {

	}

}
