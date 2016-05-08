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

use L2tpServer\AVPs\AssignedTunnelIdAVP;
use L2tpServer\AVPs\AVPFactory;
use L2tpServer\AVPs\BaseAVP;
use L2tpServer\Constants\AvpType;
use L2tpServer\Constants\Protocol;
use L2tpServer\Exceptions\ClientException;
use L2tpServer\Exceptions\CloseConnectionException;
use L2tpServer\Exceptions\TunnelException;
use Packfire\Logger\File as Logger;

class Client
{

    const TIMEOUT = 310;
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
        if ($this->packet->getType() == Packet::TYPE_CONTROL) {
            /* @var $this->packet CtrlPacket */
            $this->logger->info("Receiving control packet");
            return $this->controlRequest();
        } elseif ($this->packet->getType() == Packet::TYPE_DATA) {
            $this->logger->info("Receiving data packet");
            return $this->dataRequest();
        }
    }

    private function logAVP(CtrlPacket $packet)
    {
        foreach ($packet->getAVPS() as $avp) {
            $className = explode('\\', get_class($avp));
            $this->logger->info(
                array_pop($className) . ': ' . (is_array($avp->value) ? http_build_query(
                    $avp->value
                ) : $avp->value)
            );
        }
    }

    private function controlRequest()
    {
        /* @var $this->packet CtrlPacket */
        if ($this->packet->getAVP(AvpType::MESSAGE_TYPE_AVP) === NULL) {
            // ZLB-packet
            return new CtrlPacket();
        }
        $message_type = $this->packet->getAVP(AvpType::MESSAGE_TYPE_AVP)->value;
        $this->logger->info("control packet info: nR: " . $this->packet->Nr . ', Ns:' . $this->packet->Ns . ' Type: ' . $message_type);
        $this->incrementReceivedPacketsNumber();
        $serverTunnelId = $this->packet->tunnelId;
        if (empty($serverTunnelId) && $message_type == MT_SCCRQ) {
            /* @var $tunnelIdAvp AssignedTunnelIdAVP */
            $tunnelIdAvp = $this->packet->getAVP(AvpType::ASSIGNED_TUNNEL_ID_AVP);
            $clientTunnelId = $tunnelIdAvp->value;
            // TODO: check, what's happen if tunnel exists?
            $serverTunnelId = count($this->tunnels) + 1;
            $this->tunnels[$serverTunnelId] = new Tunnel($clientTunnelId, $serverTunnelId);
            // let's fill other properties for client:
            $this->hostname = $this->packet->getAVP(AvpType::HOSTNAME_AVP)->value;
        } elseif(empty($serverTunnelId)) { // Message is not SCCRQ, but tunnel id is empty
            throw new \Exception("Tunnel not specified for control message");
        }
        // Handle packet:
        if (isset($this->tunnels[$serverTunnelId])) {
            /* @var $tunnel Tunnel */
            $tunnel = $this->tunnels[$serverTunnelId];
            if ($message_type == MT_StopCCN) {
                $this->logger->info("Stop-Control-Connection-Notification");
                $error = $this->packet->getAVP(AvpType::RESULT_CODE_AVP)->value;
                $message = ("Result code: $error[resultCode]");
                if (isset($error['errorCode'])) {
                    $message.= ", Error code: $error[errorCode]";
                }
                if (isset($error['errorMessage'])) {
                    $message .= ", Error message: $error[errorMessage]";
                }
                $this->logger->info($message);
                unset($this->tunnels[$serverTunnelId]);
                $this->logger->info("Closing tunnel $serverTunnelId");
                throw new CloseConnectionException();
//                return new CtrlPacket();
            } else {
                $responsePacket = $tunnel->processRequest($this->packet);
            }
        } else {
            throw new ClientException("Tunnel # {$serverTunnelId} is not found");
        }
        /* @var $responsePacket CtrlPacket */
        if ($responsePacket instanceof CtrlPacket) {
            // Set client-level data:
            $responsePacket->setNs($this->sentNumber);
            $responsePacket->setNr($this->receivedNumber);
            // Don't increment for ZLB ACK messages
            if (count($responsePacket->getAVPS())) {
                $this->incrementSentPacketsNumber();
            }
        }
        return $responsePacket;
    }

    private function dataRequest()
    {
        $serverTunnelId = $this->packet->tunnelId;
        /* @var Tunnel $tunnel */
        if (!isset($this->tunnels[$serverTunnelId])) {
            $this->logger->error("I've got packet for unknown tunnel $serverTunnelId, content: " . var_export($this->packet, true));
            return null;
        }
        $tunnel = $this->tunnels[$serverTunnelId];
        return $tunnel->processRequest($this->packet);
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    protected function setTimeout()
    {
        $this->timeout = time() + self::TIMEOUT;
    }

    protected function incrementSentPacketsNumber()
    {
        $this->sentNumber = ($this->sentNumber + 1) % 65536; // We'v got a new message
    }

    protected function incrementReceivedPacketsNumber()
    {
        $this->receivedNumber = ($this->packet->Ns + 1) % 65536; // We'v got a new message
    }

    public function getTunnels()
    {
        return is_array($this->tunnels) ? $this->tunnels : array();
    }
}
