<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace L2tpServer\Constants;
/**
 * Description of class
 *
 * @author Сергей
 */
class AvpType {
	const MESSAGE_TYPE_AVP = 0;
	const RESULT_CODE_AVP = 1;
	const PROTOCOL_VERSION_AVP = 2;
	const FRAMING_CAPABILITIES_AVP = 3;
	const BEARER_CAPABILITIES_AVP = 4;
	const TIE_BREAKER_AVP = 5;
	const FIRMWARE_REVISION_AVP = 6;
	const HOSTNAME_AVP = 7;
	const VENDOR_NAME_AVP = 8;
	const ASSIGNED_TUNNEL_ID_AVP = 9;
	const RECEIVE_WINDOW_SIZE_AVP = 10;
	const CHALLENGE_AVP = 11;
	const CAUSE_CODE_AVP = 12;
	const RESPONSE_AVP = 13;
	const ASSIGNED_SESSION_ID_AVP = 14;
	const CALL_SERIAL_NUMBER_AVP = 15;
	const MINIMUM_BPS_AVP = 16;
	const MAXIMUM_BPS_AVP = 17;
	const BEARER_TYPE_AVP = 18;
	const FRAMING_TYPE_AVP = 19;
	const CALLED_NUMBER_AVP = 21;
	const CALLING_NUMBER_AVP = 22;
	const SUBADDRESS_AVP = 23;
	const TX_CONNECT_SPEED_BPS_AVP = 24;
	const PHYSICAL_CHANNEL_ID_AVP = 25;
	const INITIAL_RECEIVED_LCP_CONFREQ_AVP = 26;
	const LAST_SENT_LCP_CONFREQ_AVP = 27;
	const LAST_RECEIVED_LCP_CONFREQ_AVP = 28;
	const PROXY_AUTHEN_TYPE_AVP = 29;
	const PROXY_AUTHEN_NAME_AVP = 30;
	const PROXY_AUTHEN_CHALLENGE_AVP = 31;
	const PROXY_AUTHEN_ID_AVP = 32;
	const PROXY_AUTHEN_RESPONSE_AVP = 33;
	const CALL_ERRORS_AVP = 34;
	const ACCM_AVP = 35;
	const RANDOM_VECTOR_AVP = 36;
	const PRIVATE_GROUP_ID_AVP = 37;
	const RX_CONNECT_SPEED_AVP = 38;
	const SEQUENCE_REQUIRED_AVP = 39;

	static function exists($type) {
		if ( $type >= 0 && $type < 40 && $type != 20) {
			return true;
		}
		return false;
	}
}

?>
