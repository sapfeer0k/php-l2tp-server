<?php
/****
* This file is part of php-l2tp-server.
* Copyright (C) Sergei Lomakov <sergei@lomakov.net>
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
*****/

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$socket = socket_create(AF_INET,SOCK_DGRAM, SOL_UDP);
if ($socket < 0)
{
	printf("Error in line %d", __LINE__ - 3);
	exit();
}
if (socket_bind($socket, "0.0.0.0", "1701") == false)
{
	printf("Error in line %d",__LINE__-2);
	exit();
}

/*
 *	Server Loop
 */

$clients = array();

function __autoload($class) {
	require_once('classes/class.'.$class.'.php');
}

while(1)
{

	$len = socket_recvfrom($socket, $buf, 65535, 0, $ip, $port);
	if ($len > 0)
	{
		$client_hash = md5($ip .':'. $port);
		list( , $byte) = unpack('C',$buf[0]);
                if ( $byte & 128 ) {
			$packet = new l2tp_ctrl_packet($buf);
		} else {
			$packet = new l2tp_inf_packet($buf);
		}

		if (!isset($clients[$client_hash]) || is_object($clients[$client_hash])) {
			$clients[$client_hash] = new l2tp_client($ip, $port);
		}
		$clients[$client_hash]->processRequest($packet);
		print_r($packet);
		die();
	}
}

