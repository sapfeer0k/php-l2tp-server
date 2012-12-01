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
		return $avp;
	}

	static function parseAVP($avp_raw_data) {
		list( , $avp_type) = unpack('n', $avp_raw_data[4].$avp_raw_data[5]);

		switch($avp_type) {
			case constants_avp_type::MESSAGE_TYPE_AVP:
				$avp = new l2tp_message_type_avp($avp_raw_data);
				break;
			default:
				// default AVP
				$avp = new l2tp_unrecognized_avp($avp_raw_data);
		}
		return $avp;
	}

}
