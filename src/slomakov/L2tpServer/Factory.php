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

class Factory
{

    public static function createPacket($raw_data)
    {
        list( , $byte) = unpack('C', $raw_data[0]);
        if ($byte & Packet::TYPE_CONTROL) {
            $packet = new CtrlPacket($raw_data);
        } else {
            $packet = new DataPacket($raw_data);
        }
        return $packet;
    }
}
