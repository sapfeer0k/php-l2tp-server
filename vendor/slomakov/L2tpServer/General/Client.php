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

use L2tpServer\AVPs\AVPFactory;
use L2tpServer\AVPs\BaseAVP;
use L2tpServer\Constants\AvpType;
use L2tpServer\Constants\Protocol;
use L2tpServer\Exceptions\ClientException;
use Packfire\Logger\File as Logger;

class Client
{

    const TIMEOUT = 31;
    protected $logger;
    protected $receivedNumber = 0;
    protected $sentNumber = 0;
    private $ip_addr;
    private $port;
    /* @var $packet Packet */
    private $packet;
    private $hostname;
    private $tunnels;
    private $timeout;

    function __construct($remote_addr, $remote_port)
    {
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
    function processRequest(Packet $packet)
    {
        if (!isset($packet) || !is_object($packet)) {
            throw new \Exception("Packet object isn't valid");
        }
        $this->setTimeout();
        $this->packet = $packet;
        if ($this->packet->packetType == Packet::TYPE_CONTROL) {
            $this->logger->info("Receiving control packet");
            return $this->controlRequest();
        } elseif ($this->packet->packetType == Packet::TYPE_DATA) {
            $this->logger->info("Receiving data packet");
            return $this->dataRequest();
        }
    }

    private function controlRequest()
    {
        /* @var $this->packet CtrlPacket */
        $this->logger->info("Receiving control packet: " . ($this->packet->Nr . ':' . $this->packet->Ns));
        $message_type = $this->packet->getAVP(AvpType::MESSAGE_TYPE_AVP)->value;
        $this->receivedNumber = ($this->receivedNumber + 1) % 65536; // We'v got a new message
        switch ($message_type) {
            case MT_SCCRQ:
                $this->logger->info("Start-Control-Connection-Request");
                foreach ($this->packet->getAVPS() as $avp) {
                    $className = explode('\\', get_class($avp));
                    $this->logger->info(
                        array_pop($className) . ': ' . (is_array($avp->value) ? http_build_query(
                            $avp->value
                        ) : $avp->value)
                    );
                }
                // AVPs which must be present:
                $this->tunnelIdAvp = $this->packet->getAVP(AvpType::ASSIGNED_TUNNEL_ID_AVP);
                if (!$this->tunnelIdAvp instanceof BaseAVP) {
                    throw new ClientException("Tunnel ID avp is not found");
                }
                // TODO: check, what's happen if tunnel exists?
                $this->tunnels[$this->tunnelIdAvp->value] = new Tunnel($this->tunnelIdAvp->value); // Save new tunnel:
                // let's fill other properties:
                $this->hostname = $this->packet->getAVP(AvpType::HOSTNAME_AVP)->value;
                $clientVersion = $this->packet->getAVP(AvpType::PROTOCOL_VERSION_AVP);
                if (!$clientVersion instanceof BaseAVP) {
                    throw new ClientException("Client protocol version does not specified");
                }
                $framingCapabilities = $this->packet->getAVP(AvpType::FRAMING_CAPABILITIES_AVP);
                // TODO: Check framing capabilities & protocol version
                // AVPs that may be present: Bearer Capabilities , Receive Window Size , Challenge,
                // Tie Breaker, Firmware Revision , Vendor Name
                /* build response: */
                $responsePacket = $this->generateSCCRP();
                break;
            default:
                var_dump($this->packet);
                throw new \Exception("Unknown packet type");
                // This is not control tunnel message, let it be handled by session:
                $tunnel_id = $this->packet->tunnelId;
        }
        $this->sentNumber = ($this->sentNumber + 1) % 65536; // We'v got a new message
        foreach ($responsePacket->getAVPS() as $avp) {
            $className = explode('\\', get_class($avp));
            $this->logger->info(
                array_pop($className) . ': ' . (is_array($avp->value) ? http_build_query($avp->value) : $avp->value)
            );
        }
        $responsePacket->setTunnelId($this->packet->getAVP(AvpType::ASSIGNED_TUNNEL_ID_AVP)->value);
        return $responsePacket;
        /*
                if (isset($this->tunnels[$tunnel_id])) {
                    $this->tunnels[$tunnel_id]->processRequest($this->packet->avps);
                    // TODO: Set L2tpServer packet header for the response packet
                } else {
                    throw new TunnelException("Tunnel # ${tunnel_id} is not found");
                }
        */
    }

    private function generateSCCRP()
    {
        $this->logger->info("Start-Control-Connection-Reply");
        $responsePacket = new CtrlPacket();
        $responsePacket->setNs($this->sentNumber);
        $responsePacket->setNr($this->receivedNumber);
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
        $avp->setValue($this->tunnelIdAvp->value);
        $responsePacket->addAVP($avp);
        $avp = AVPFactory::createAVP(AvpType::BEARER_CAPABILITIES_AVP);
        $avp->setValue(null);
        $responsePacket->addAVP($avp);
        $avp = AVPFactory::createAVP(AvpType::FIRMWARE_REVISION_AVP);
        // TODO: set this to constant:
        $avp->setValue(1);
        $responsePacket->addAVP($avp);
        $avp = AVPFactory::createAVP(AvpType::VENDOR_NAME_AVP);
        $avp->setValue(NULL);
        $responsePacket->addAVP($avp);
        $avp = AVPFactory::createAVP(AvpType::RECEIVE_WINDOW_SIZE_AVP);
        // TODO: put this value into constant
        $avp->setValue(1024);
        $responsePacket->addAVP($avp);
        return $responsePacket;
    }

    private function dataRequest()
    {

    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    protected function setTimeout()
    {
        $this->timeout = time() + self::TIMEOUT;
    }

}
