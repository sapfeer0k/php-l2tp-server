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

use L2tpServer\Constants\AvpType;

class ReceiveWindowSizeAVP extends BaseAVP
{

    public function __construct()
    {
        $this->type = AvpType::RECEIVE_WINDOW_SIZE_AVP;
        $this->is_mandatory = 1;
        $this->is_hidden = 0;
        parent::__construct();
    }

	public static function import($data) {
        $avp = new self();
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$avp->is_mandatory = ($avp_flags_len & 32768) ? true : false;
		$avp->is_hidden = ($avp_flags_len & 16384) ? true : false;
		$avp->length = ($avp_flags_len & 1023);
		if ($avp->length != 8 ) {
			throw new Exception_IgnoreAVP("Invalid length for Receive Window Size AVP");
		}
		list( , $avp->vendor_id) = unpack('n', $data[2].$data[3]);
		list( , $avp->type) = unpack('n', $data[4].$data[5]);
		list( , $avp->value) = unpack('n', $data[6].$data[7]);
		$avp->validate();
        return $avp;
	}

	public function setValue($value) {
		if ($value > 0 && $value < 0xFFFF) {
			$this->value = $value;
		} else {
			throw new Exception("Invalid value for Receive Window Size AVP");
		}
		return true;
	}

    protected function getEncodedValue()
    {
        return pack('n', $this->value);
    }

	protected function validate() {
		if (!$this->is_mandatory) {
			throw new Exception_IgnoreAVP("Receive Window Size AVP should not be mandatory.");
		}
		if ($this->is_hidden) {
			throw new Exception_IgnoreAVP("Receive Window Size AVP should not be hidden.");
		}
	}
}

?>
