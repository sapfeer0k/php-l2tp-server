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


class avp_factory {

	static function createAVP($avp_type, $avp_raw_data) {
		switch($avp_type) {
			case MESSAGE_TYPE_AVP:
				$avp = new l2tp_message_type_avp($avp_raw_data);
				break;
			default:
				// default AVP
				$avp = new l2tp_default_avp_type($avp_raw_data);
		}
		return $avp;
	}

}
