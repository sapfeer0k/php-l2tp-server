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
use L2tpServer\Constants\AvpType;
use L2tpServer\Exceptions\ClientException;
use L2tpServer\Exceptions\CloseConnectionException;
use L2tpServer\Exceptions\ServerException;
use L2tpServer\Tools\Logger;
use L2tpServer\Tools\TLogger;


class Client
{
    use TLogger;

    const TIMEOUT = 310;
    protected $receivedNumber = 0;
    protected $sentNumber = 0;
    protected $socket;
    private $ip_addr;
    private $port;
    private $packet;
    private $hostname;
    private $tunnels;
    private $timeout;

    /**
     * Client constructor.
     * @param $remoteAddr
     * @param $remotePort
     */
    public function __construct($remoteAddr, $remotePort)
    {
        if (filter_var($remoteAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->ip_addr = $remoteAddr;
        } else {
            throw new \Exception("Client IP address isn't valid");
        }
        if ($remotePort > 0 && $remotePort < 65535) {
            $this->port = $remotePort;
        } else {
            throw new \Exception("Client port isn't valid");
        }
        $this->tunnels = array();
        return true;
    }

    public static function factory($socket, $address, $port, Logger $logger = null)
    {
        $instance = new static($address, $port);
        if (!is_null($logger)) {
            $instance->setLogger($logger);
        }
        $instance->setSocket($socket);
        return $instance;
    }

    /**
     * @param $socket
     * @throws \Exception
     */
    public function setSocket($socket)
    {
        if (!is_resource($socket)) {
            throw new \Exception("Invalid socket specified: " . json_encode($socket));
        }
        $this->socket = &$socket;
    }

    /**
     * @param Packet $packet
     * @return Packet|null
     * @throws ClientException
     * @throws CloseConnectionException
     * @throws \Exception
     */
    public function processRequest(Packet $packet)
    {
        if (!isset($packet) || !is_object($packet)) {
            throw new \Exception("Packet object isn't valid");
        }
        $this->setTimeout();
        if ($packet->getType() == Packet::TYPE_CONTROL) { /* @var $packet CtrlPacket */
            $this->setPacket($packet);
            return $this->controlRequest();
        } elseif ($packet->getType() == Packet::TYPE_DATA) {
            $serverTunnelId = $packet->getTunnelId();
            /* @var Tunnel $tunnel */
            if (!isset($this->tunnels[$serverTunnelId])) {
                $message = "Got packet for unknown tunnel $serverTunnelId";
                $this->getLogger()->error($message);
                return null;
            }
            return $this->tunnels[$serverTunnelId]->processRequest($packet);
        } else {
            $this->getLogger()->warning("Unknown packet type: {$packet->getType()}");
            return null;
        }
    }

    /**
     * @return CtrlPacket|Packet|null
     * @throws ClientException
     * @throws CloseConnectionException
     * @throws \Exception
     */
    private function controlRequest()
    {
        /* @var $this ->packet CtrlPacket */
        if ($this->getPacket()->getAVP(AvpType::MESSAGE_TYPE_AVP) === null) {
            // ZLB-packet
            return CtrlPacket::factory();
        }
        $message_type = $this->getPacket()->getAVP(AvpType::MESSAGE_TYPE_AVP)->value;
        $this->getLogger()->info(
            "control packet info: nR: " . $this->getPacket()->getNumberReceived() .
            ', Ns:' . $this->getPacket()->getNumberSent() .
            ' Type: ' . $message_type
        );
        $this->incrementReceivedPacketsNumber();
        $serverTunnelId = $this->getPacket()->getTunnelId();
        if (empty($serverTunnelId) && $message_type == MT_SCCRQ) {
            /* @var $tunnelIdAvp AssignedTunnelIdAVP */
            $tunnelIdAvp = $this->getPacket()->getAVP(AvpType::ASSIGNED_TUNNEL_ID_AVP);
            $clientTunnelId = $tunnelIdAvp->value;
            // TODO: check, what's happen if tunnel exists?
            $serverTunnelId = count($this->tunnels) + 1;
            $tunnel = new Tunnel($clientTunnelId, $serverTunnelId);
            $tunnel->setLogger($this->getLogger());
            $this->tunnels[$serverTunnelId] = $tunnel;
            // let's fill other properties for client:
            $this->hostname = $this->getPacket()->getAVP(AvpType::HOSTNAME_AVP)->value;
        } elseif (empty($serverTunnelId)) { // Message is not SCCRQ, but tunnel id is empty
            throw new \Exception("Tunnel not specified for control message");
        }
        // Handle packet:
        if (isset($this->tunnels[$serverTunnelId])) {
            /* @var $tunnel Tunnel */
            $tunnel = $this->tunnels[$serverTunnelId];
            if ($message_type == MT_STOP_CCN) {
                $this->getLogger()->info("Stop-Control-Connection-Notification");
                $error = $this->getPacket()->getAVP(AvpType::RESULT_CODE_AVP)->value;
                $message = ("Result code: $error[resultCode]");
                if (isset($error['errorCode'])) {
                    $message .= ", Error code: $error[errorCode]";
                }
                if (isset($error['errorMessage'])) {
                    $message .= ", Error message: $error[errorMessage]";
                }
                $this->getLogger()->info($message);
                unset($this->tunnels[$serverTunnelId]);
                $this->getLogger()->info("Closing tunnel $serverTunnelId");
                throw new CloseConnectionException();
            } else {
                $responsePacket = $tunnel->processRequest($this->getPacket());
            }
        } else {
            throw new ClientException("Tunnel # {$serverTunnelId} is not found");
        }
        /* @var $responsePacket CtrlPacket */
        if ($responsePacket instanceof CtrlPacket) {
            // Set client-level data:
            $responsePacket->setNumberSent($this->sentNumber);
            $responsePacket->setNumberReceived($this->receivedNumber);
            // Don't increment for ZLB ACK messages
            if ($responsePacket->getAvpCount()) {
                $this->incrementSentPacketsNumber();
            }
        }
        return $responsePacket;
    }

    /**
     *
     */
    protected function incrementReceivedPacketsNumber()
    {
        $this->receivedNumber = ($this->getPacket()->getNumberSent() + 1) % 65536; // We'v got a new message
    }

    /**
     *
     */
    protected function incrementSentPacketsNumber()
    {
        $this->sentNumber = ($this->sentNumber + 1) % 65536; // We'v got a new message
    }

    /**
     * @return mixed
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     *
     */
    protected function setTimeout()
    {
        $this->timeout = time() + self::TIMEOUT;
    }

    /**
     * @return Tunnel[]
     */
    public function getTunnels()
    {
        return is_array($this->tunnels) ? $this->tunnels : array();
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip_addr;
    }

    /**
     * @param $data
     * @return integer|false
     */
    public function send($data)
    {
        $retCode = socket_sendto($this->socket, $data, strlen($data), 0, $this->ip_addr, $this->port);
        /*
        $this->getLogger()->info(
            "Response to: {$this->ip_addr}:{$this->port}" .
            ", data: " . bin2hex($data) . "(" . strlen($data) . ' bytes)" .
            ", actually written: ' . $retCode
        );
        */
        return $retCode;
    }

    protected function setPacket(CtrlPacket $packet)
    {
        $this->packet = $packet;
        $this->packet->setLogger($this->getLogger());
    }

    protected function getPacket()
    {
        if (!$this->packet instanceof CtrlPacket) {
            throw new ServerException("Packet object is not set");
        }
        return $this->packet;
    }
}
