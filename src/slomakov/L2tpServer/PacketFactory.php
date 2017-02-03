<?php
/****
* This file is part of php-L2tpServer-server.
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
*****/

namespace L2tpServer;

use L2tpServer\General\CtrlPacket;
use L2tpServer\General\DataPacket;
use L2tpServer\General\Packet;

class PacketFactory
{
    /**
     * @param $rawData
     * @return Packet
     * @throws \Exception
     */
    public static function parse($rawData)
    {
        list( , $byte) = unpack('C', $rawData[0]);
        if ($byte & Packet::TYPE_CONTROL) {
            $packet = CtrlPacket::factory()->parse($rawData);
        } else {
            $packet = DataPacket::factory()->parse($rawData);
        }
        return $packet;
    }

    public static function generateZLB()
    {
        $responsePacket = CtrlPacket::factory();
        return $responsePacket;
    }
}
