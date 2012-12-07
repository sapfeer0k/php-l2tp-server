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
 * Description of server
 *
 * @author "Sergei Lomakov <sergei@lomakov.net>"
 */
class L2tp_Server {
	//put your code here
	private $addr;
	private $port;
	private $socket;
	private $clients;

	function __construct($addr="0.0.0.0", $port=1701) {
		$this->clients = array();
		$this->addr = $addr;
		$this->port = $port;
		# create socket:
		$this->socket = socket_create(AF_INET,SOCK_DGRAM, SOL_UDP);
		if ($this->socket < 0)
		{
			throw new ServerException("Can't create socket.");
		}

		return ;
	}

	function run() {
		if (socket_bind($this->socket, $this->addr, $this->port) == false)
		{
			throw new ServerException("Can't bind on address.");
		}

		while(1) {
			$buf = NULL;
			$ip = NULL;
			$port = NULL;
			$len = socket_recvfrom($this->socket, $buf, 65535, 0, $ip, $port);
			if ($len > 0) {
				$client_hash = md5($ip .':'. $port);
				$packet = Factory::createPacket($buf);
				// Is it new client ?
				if (!isset($this->clients[$client_hash]) || is_object($this->clients[$client_hash])) {
					$this->clients[$client_hash] = new L2tp_Client($ip, $port);
				}
				$answer = $this->clients[$client_hash]->processRequest($packet);
				die();
				print_r($answer);
			}
		}
	}
}

?>
