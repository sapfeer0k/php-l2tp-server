<?php
/****
* This file is part of php-l2tp-server.
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


define("CONN_STATE_NULL", 0);
#define("CONN_STATE_


class L2tp_Client  {

	private $ip_addr;
	private $port;

	private $packet;
	private $hostname;
	private $tunnels;

	function __construct($remote_addr, $remote_port) {
		if (filter_var($remote_addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$this->ip_addr = $remote_addr;
		} else {
			throw new Exception("Client IP address isn't valid");
		}
		if ($remote_port > 0 && $remote_port < 65535) {
			$this->port = $remote_port;
		} else {
			throw new Exception("Client port isn't valid");
		}
		$this->tunnels = array();
		return true;
	}

	// Packet is object
	function processRequest($packet) {
		if(!isset($packet) || !is_object($packet)) {
			throw new exception("Packet object isn't valid");
		}
		$this->packet = $packet;
		if ($this->packet->packet_type == PACKET_TYPE_CONTROL) {
			$this->controlRequest();
		} elseif ($this->packet->packet_type == PACKET_TYPE_DATA) {

		}
	}

	private function controlRequest() {
		$message_type = $this->packet->getAVP(MESSAGE_TYPE_AVP)->value;
		switch($message_type) {
			case MT_SCCRQ:
				// AVP that must be present:
				$tunnel_id = $this->packet->getAVP(ASSIGNED_TUNNEL_ID_AVP)->value;
				// let's fill other properties:
				$this->hostname = $this->packet->getAVP(HOSTNAME_AVP)->value;
				// TODO: Check framing capabilities & protocol version

				// AVP that may be present: Bearer Capabilities , Receive Window Size , Challenge,
				// Tie Breaker, Firmware Revision , Vendor Name
				$challenge = $this->packet->getAVP(CHALLENGE_AVP);

				// Save new tunnel:
				$this->tunnels[$tunnel_id] = new L2tp_Tunnel($this->packet->avps);
			break;
			default:
				// This is not control tunnel message, let it be handled by session:
				$tunnel_id = $this->packet->tunnel_id;
		}

		if (isset($this->tunnels[$tunnel_id])) {
			$this->tunnels[$tunnel_id]->processRequest();
			// TODO: Set l2tp packet header for the response packet
		} else {
			throw new Exception_Tunnel("Tunnel # ${tunnel_id} is not found");
		}

	}

	private function dataRequest() {

	}

}
