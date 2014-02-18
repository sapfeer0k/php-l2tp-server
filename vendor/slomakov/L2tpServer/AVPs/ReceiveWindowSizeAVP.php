<?php

/*
 * This file is a part of php-L2tpServer-server.
 * Copyright (C) "Sergei Lomakov <sergei@lomakov.net>"
 *
 * php-L2tpServer-server is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * php-L2tpServer-server is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with php-L2tpServer-server.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace L2tpServer\AVPs;

class ReceiveWindowSizeAVP extends BaseAVP {
	//put your code here

	protected function parse($data) {
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$this->is_mandatory = ($avp_flags_len & 32768) ? true : false;
		$this->is_hidden = ($avp_flags_len & 16384) ? true : false;
		$this->length = ($avp_flags_len & 1023);
		if ($this->length != 8 ) {
			throw new Exception_IgnoreAVP("Invalid length for Receive Window Size AVP");
		}
		list( , $this->vendor_id) = unpack('n', $data[2].$data[3]);
		list( , $this->type) = unpack('n', $data[4].$data[5]);
		list( , $this->value) = unpack('n', $data[6].$data[7]);
		$this->validate();
	}

	function setValue($value) {
		if ($value > 0 && $value < 0xFFFF) {
			$this->value = $value;
		} else {
			throw new Exception("Invalid value for Receive Window Size AVP");
		}
		return true;
	}

	function encode() {
		throw new Exception("Encode method isn't defined");
	}

	function validate() {
		if (!$this->is_mandatory) {
			throw new Exception_IgnoreAVP("Receive Window Size AVP should not be mandatory.");
		}
		if ($this->is_hidden) {
			throw new Exception_IgnoreAVP("Receive Window Size AVP should not be hidden.");
		}
	}
}

?>
