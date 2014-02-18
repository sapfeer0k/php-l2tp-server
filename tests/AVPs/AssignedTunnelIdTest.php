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

/**
 * Description of test_message_type_avp
 *
 * @author "Sergei Lomakov <sergei@lomakov.net>"
 */

use L2tpServer\AVPs\AssignedTunnelIdAVP;

class AssignedTunnelIdAVPTest extends PHPUnit_Framework_TestCase {


	public function testConstructor() {
		$avp = new AssignedTunnelIdAVP();
		$this->assertEquals(true, $avp->is_mandatory);
	}

	/**
	 * @dataProvider providerProperValues
	 */
	public function testEncode($tunnel_id) {
		$avp = new AssignedTunnelIdAVP();

		if ($tunnel_id == 0) {
			$this->setExpectedException('Exception');
		}

		$avp->setValue($tunnel_id);
		$binary_data = $avp->encode();
		$test_avp = new AssignedTunnelIdAVP($binary_data);
		$this->assertEquals($tunnel_id, $test_avp->value);
	}

	// Data providers:
	public function providerProperValues() {
		for($value=0; $value < 0xFFFF; $value+=mt_rand(1,500)) {
			$tids[] = array($value);
		}
		return $tids;
	}
}

?>
