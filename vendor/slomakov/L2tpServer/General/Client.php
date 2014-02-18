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


namespace L2tpServer\General;

use L2tpServer\AVPs\AVPFactory,
    L2tpServer\AVPs\BaseAVP,
    L2tpServer\Exceptions\ClientException,
    L2tpServer\Constants\AvpType,
    Packfire\Logger\File as Logger,
    L2tpServer\Constants\Protocol;

class Client  {

    const TIMEOUT = 31;
	private $ip_addr;
	private $port;

    /* @var $packet Packet */
	private $packet;
	private $hostname;
	private $tunnels;
    private $timeout;
    protected $logger;

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
        $this->logger = new Logger('server.log');
		return true;
	}

	// Packet is object
	function processRequest($packet) {
		if(!isset($packet) || !is_object($packet)) {
			throw new \Exception("Packet object isn't valid");
		}
        $this->setTimeout();
		$this->packet = $packet;
		if ($this->packet->packet_type == Packet::TYPE_CONTROL) {
			return $this->controlRequest();
		} elseif ($this->packet->packet_type == Packet::TYPE_DATA) {
            $this->logger->info("Receiving data packet");
            return $this->dataRequest();
		}
	}

    protected function setTimeout()
    {
        $this->timeout = time() + self::TIMEOUT;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

	private function controlRequest()
    {
        /* @var $this->packet CtrlPacket */
        $this->logger->info("Receiving control packet");
		$message_type = $this->packet->getAVP(AvpType::MESSAGE_TYPE_AVP)->value;
		switch($message_type) {
			case MT_SCCRQ:
				// AVPs that must be present:
                $tunnelIdAvp = $this->packet->getAVP(AvpType::ASSIGNED_TUNNEL_ID_AVP);
				if (!$tunnelIdAvp instanceof BaseAVP) {
                    throw new ClientException("Tunnel ID avp is not found");
                }
                // TODO: check, what's happen if tunnel exists?
                $this->tunnels[$tunnelIdAvp->value] = new Tunnel($tunnelIdAvp->value); // Save new tunnel:
				// let's fill other properties:
				$this->hostname = $this->packet->getAVP(AvpType::HOSTNAME_AVP)->value;
                $clientVersion = $this->packet->getAVP(AvpType::PROTOCOL_VERSION_AVP);
                if (!$clientVersion instanceof BaseAVP) {
                    throw new ClientException("Client protocol version does not specified");
                }
				// TODO: Check framing capabilities & protocol version
				// AVPs that may be present: Bearer Capabilities , Receive Window Size , Challenge,
				// Tie Breaker, Firmware Revision , Vendor Name
                /* build response: */
                $responsePacket = new CtrlPacket();
                // Add message type:
                $avp = AVPFactory::createAVP(AvpType::MESSAGE_TYPE_AVP);
                $avp->setValue(MT_SCCRP);
                $responsePacket->addAVP($avp);
                // Add protocol version:
                $avp = AVPFactory::createAVP(AvpType::PROTOCOL_VERSION_AVP);
                $avp->setValue(array('version' => Protocol::VERSION, 'revision' => Protocol::REVISION));
                $responsePacket->addAVP($avp);
                // Add framing capabilities:
                $avp = AVPFactory::createAVP(AvpType::FRAMING_CAPABILITIES_AVP);
                $avp->setValue();
                $responsePacket->addAVP($avp);
                // Set host name:
                $avp = AVPFactory::createAVP(AvpType::HOSTNAME_AVP);
                $avp->setValue('lomakov.net');
                $responsePacket->addAVP($avp);
                // Add host name:
                $avp = AVPFactory::createAVP(AvpType::ASSIGNED_TUNNEL_ID_AVP);
                $avp->setValue($tunnelIdAvp->value);
                $responsePacket->addAVP($avp);
                return $responsePacket;
				break;
			default:
				// This is not control tunnel message, let it be handled by session:
				$tunnel_id = $this->packet->tunnel_id;
		}

		if (isset($this->tunnels[$tunnel_id])) {
			$this->tunnels[$tunnel_id]->processRequest($this->packet->avps);
			// TODO: Set L2tpServer packet header for the response packet
		} else {
			throw new TunnelException("Tunnel # ${tunnel_id} is not found");
		}

	}

	private function dataRequest() {

	}

}
