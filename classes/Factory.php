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


class factory {

	static function createPacket($raw_data) {
		list( , $byte) = unpack('C',$raw_data[0]);
        if ( $byte & 128 ) {
			$packet = new l2tp_ctrl_packet($raw_data);
		} else {
			$packet = new l2tp_data_packet($raw_data);
		}
		return $packet;
	}

	static function createAVP($type, $value) {
		$avp = false;
		// is mandatory -> always true!
		return $avp;
	}

	static function parseAVP($avp_raw_data) {
		list( , $first_byte) = unpack('C', $avp_raw_data[0]);
		if ( $first_byte & 60 ) { // check for reserved bits
			$avp_type = 'unrecognised';
		}
		list( , $avp_type) = unpack('n', $avp_raw_data[4].$avp_raw_data[5]);

		switch($avp_type) {
			case constants_avp_type::MESSAGE_TYPE_AVP:
				$avp = new l2tp_message_type_avp($avp_raw_data);
				break;
			case constants_avp_type::PROTOCOL_VERSION_AVP:
				$avp = new l2tp_protocol_version_avp($avp_raw_data);
				break;
			case constants_avp_type::HOSTNAME_AVP:
				$avp = new l2tp_hostname_avp($avp_raw_data);
				break;
			case constants_avp_type::FRAMING_CAPABILITIES_AVP:
				$avp = new l2tp_framing_capabilities_avp($avp_raw_data);
				break;
			case constants_avp_type::BEARER_CAPABILITIES_AVP:
				$avp = new l2tp_bearer_capabilities_avp($avp_raw_data);
				break;
			case constants_avp_type::FIRMWARE_REVISION_AVP:
				$avp = new l2tp_firmware_revision_avp($avp_raw_data);
				break;
			case constants_avp_type::ASSIGNED_TUNNEL_ID_AVP:
				$avp = new l2tp_assigned_tunnel_id_avp($avp_raw_data);
				break;
			default:
				// default AVP
				echo("AVP TYPE IS $avp_type");
				$avp = new l2tp_unrecognized_avp($avp_raw_data);
		}
		return $avp;
	}

}
