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

use L2tpServer\AVPs\BearerCapabilitiesAVP;

class BearerCapabilitiesTest extends PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        // TODO: implement hiding
        $avp = new BearerCapabilitiesAVP(0);
        $avp->setValue(0);
        $this->assertEquals(1, $avp->is_mandatory, "Bearer Capability AVP must be mandatory");
        $this->assertEquals(\L2tpServer\Constants\AvpType::BEARER_CAPABILITIES_AVP, $avp->type, "Type mismatch for Bearer Capability AVP");
        return $avp;
    }

    /**
     * @depends testConstructor
     */
    public function testDecoder($avp)
    {
        $rawData = $avp->encode();
        // test that two objects are equal:
        $importedAvp = BearerCapabilitiesAVP::import($rawData);
        $this->assertEquals($avp, $importedAvp, "Decoder doesn't work in Bearer Capabilities AVP");
    }

}

?>
