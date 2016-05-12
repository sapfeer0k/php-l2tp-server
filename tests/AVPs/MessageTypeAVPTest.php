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

use L2tpServer\AVPs\MessageTypeAVP;
use L2tpServer\Constants\AvpType;

class MessageTypeAVPTest extends PHPUnit_Framework_TestCase
{

	public function testConstructor() {
		$avp = new MessageTypeAVP();
		$this->assertEquals(true, $avp->isMandatory);
		$this->assertEquals(false, $avp->isHidden);
	}

	/**
	 * @dataProvider providerValues
	 */
	public function testValue($value) {
		$avp = new MessageTypeAVP();
		if ($value < 0 || $value > 65535) {
			$this->setExpectedException('Exception');
		}
		$avp->setValue($value);
	}

	/**
	 * @dataProvider providerTypes
	 */
	public function testEncode($message_type) {
		$avp = new MessageTypeAVP();
		$avp->setValue($message_type);
		$binary_data = $avp->encode();
        // checks:
        $test_avp = MessageTypeAVP::import($binary_data);
		$this->assertEquals($avp, $test_avp);
	}

	// Providers
	public function providerTypes() {
        // 0-16 allowed
        $message_types = array();
        for($i=0; $i<=16; $i++) {
            $message_types[] = array($i);
        }
		return $message_types;
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
