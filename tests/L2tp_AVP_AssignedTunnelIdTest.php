<?php

/*
 * This file is a part of php-l2tp-server.
 * Copyright (C) "Sergei Lomakov <sergei@lomakov.net>"
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
 */

/**
 * Description of test_message_type_avp
 *
 * @author "Sergei Lomakov <sergei@lomakov.net>"
 */

require_once 'PHPUnit/Autoload.php';
require_once 'Autoloader.php';

spl_autoload_register(array('Autoloader' , 'load'));

class L2tp_AVP_AssignedTunnelIdTest extends PHPUnit_Framework_TestCase {


	public function testConstructor() {
		$avp = new L2tp_AVP_AssignedTunnelId();
		$this->assertEquals(true, $avp->is_mandatory);
	}

	/**
	 * @dataProvider providerValues
	 */
	public function testValue($value) {
		$avp = new L2tp_AVP_MessageType();
		if ($value <0 || $value >= 0xFFFF) {
			$this->setExpectedException('Exception');
		}
		$avp->setValue($value);
	}

	/**
	 * @dataProvider providerProperValues
	 */
	public function testEncode($tunnel_id) {
		$avp = new L2tp_AVP_AssignedTunnelId();

		if ($tunnel_id == 0) {
			$this->setExpectedException('Exception');
		}

		$avp->setValue($tunnel_id);
		$binary_data = $avp->encode();
		$test_avp = new L2tp_AVP_AssignedTunnelId($binary_data);
		$this->assertEquals($tunnel_id, $test_avp->value);
	}

	// Providers
	public function providerProperValues() {
		for($value=0; $value < 0xFFFF; $value+=mt_rand(1,500)) {
			$tids[] = array($value);
		}
		return $tids;
	}

	public function providerValues() {
		$values = array();
		$values[] = array( -1 );
		$values[] = array( 1 );
		$values[] = array( 15 );
		$values[] = array( 34 );
		$values[] = array(99999);
		return $values;
	}

}

?>
