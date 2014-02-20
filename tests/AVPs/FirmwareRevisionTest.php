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

use \L2tpServer\AVPs\FirmwareRevisionAVP,
    \L2tpServer\Constants\AvpType;

class FirmwareRevisionTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider firmwareVersions
     */
    public function testConstructor($version)
    {
        // TODO: implement hiding
        $avp = new FirmwareRevisionAVP(0);
        if ($version < 0 || $version >= 0xFFFF) {
            $this->setExpectedException('\L2tpServer\Exceptions\AvpException');
        }
        $avp->setValue($version);
        $this->assertEquals(0, $avp->is_mandatory, "Firmware Revision AVP must not be mandatory");
        $this->assertEquals(AvpType::FIRMWARE_REVISION_AVP, $avp->type, "Type mismatch for Firmware Revision AVP");
        return $avp;
    }

    /**
     * @depends testConstructor
     */
    public function testDecoder($avp)
    {
        if (!$avp instanceof FirmwareRevisionAVP) {
            return false;
        }
        $rawData = $avp->encode();
        // test that two objects are equal:
        $importedAvp = FirmwareRevisionAVP::import($rawData);
        $this->assertEquals($avp, $importedAvp, "Decoder doesn't work in Firmware Revision AVP");
    }

    public function firmwareVersions()
    {
        return array(
            array(-1),
            array(5),
            array(100),
            array(1000),
            array(50000),
            array(100000),
        );
    }
}

?>
