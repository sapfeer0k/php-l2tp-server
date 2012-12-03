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
#require_once 'PHPUnit/Framework.php';
require_once 'classes/avps/class.l2tp_avp.php';
require_once 'classes/avps/class.l2tp_message_type_avp.php';

class l2tp_message_type_avpTest extends PHPUnit_Framework_TestCase {


	public function testConstructor() {
		$avp = new l2tp_message_type_avp();
		$this->assertEquals(true, $avp->is_mandatory);
		$this->assertEquals(false, $avp->is_hidden);
	}

	/**
	 * @dataProvider providerValues
	 */
	public function testValue($value) {
		$avp = new l2tp_message_type_avp();
		if ($value <0 || $value > 65535) {
			$this->setExpectedException('Exception');
		}
		$avp->setValue($value);
	}
/*
	public function testEncode($value) {
		$avp = new l2tp_message_type_avp();
		$avp->setValue($value);
		$avp->encode();
	}
*/
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