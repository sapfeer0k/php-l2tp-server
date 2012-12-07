<?php
/****
* This file is part of php-l2tp-server.
*
* php-l2tp-server is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* php-l2tp-server is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with php-l2tp-server.  If not, see <http://www.gnu.org/licenses/>.
*
*****/


class Factory {

	static function createPacket($raw_data) {
		list( , $byte) = unpack('C',$raw_data[0]);
        if ( $byte & 128 ) {
			$packet = new L2tp_CtrlPacket($raw_data);
		} else {
			$packet = new l2tp_data_packet($raw_data);
		}
		return $packet;
	}

	static function createAVP($params) {
		$avp_raw_data = false;
		$avp_type = NULL;
		// --
		if (isset($params['avp_raw_data'])) {
			$avp_raw_data = $params['avp_raw_data'];
			list( , $first_byte) = unpack('C', $avp_raw_data[0]);
			if ( $first_byte & 60 ) { // check for reserved bits
				$avp_type = 'unrecognised';
			}
			list( , $avp_type) = unpack('n', $avp_raw_data[4].$avp_raw_data[5]);
		} elseif (isset($params['avp_type'])) {
			// ---
			$avp_type = $params['avp_type'];
		} else {
			throw new Exception("Unknown parameters for ".__METHOD__.".");
		}

		switch($avp_type) {
			case Constants_AvpType::MESSAGE_TYPE_AVP:
				$avp = new L2tp_AVP_MessageType($avp_raw_data);
				break;
			case Constants_AvpType::PROTOCOL_VERSION_AVP:
				$avp = new L2tp_AVP_ProtocolVersion($avp_raw_data);
				break;
			case Constants_AvpType::HOSTNAME_AVP:
				$avp = new L2tp_AVP_Hostname($avp_raw_data);
				break;
			case Constants_AvpType::FRAMING_CAPABILITIES_AVP:
				$avp = new L2tp_AVP_FramingCapabilities($avp_raw_data);
				break;
			case Constants_AvpType::BEARER_CAPABILITIES_AVP:
				$avp = new L2tp_AVP_BearerCapabilities($avp_raw_data);
				break;
			case Constants_AvpType::FIRMWARE_REVISION_AVP:
				$avp = new L2tp_AVP_FirmwareRevision($avp_raw_data);
				break;
			case Constants_AvpType::ASSIGNED_TUNNEL_ID_AVP:
				$avp = new L2tp_AVP_AssignedTunnelId($avp_raw_data);
				break;
			case Constants_AvpType::RECEIVE_WINDOW_SIZE_AVP:
				$avp = new L2tp_AVP_ReceiveWindowSize($avp_raw_data);
				break;
			default:
				// default AVP
				echo("AVP TYPE IS $avp_type");
				$avp = new L2tp_AVP_Unrecognized($avp_raw_data);
		}
		return $avp;
	}

}
