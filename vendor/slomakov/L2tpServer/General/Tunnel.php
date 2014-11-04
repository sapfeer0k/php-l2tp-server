<?php

/****
* This file is part of php-L2tpServer-server.
* Copyright (C) Sergei Lomakov <sergei@lomakov.net>
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
use L2tpServer\Constants\AvpType;
use L2tpServer\Constants\Protocol;
use L2tpServer\Constants\TunnelState;
use Packfire\Logger\File as Logger;

class Tunnel
{

    private $id;
    private $internalId;
    private $sessions;
    // TODO: implement all tunnel properties here
    /* @var Logger */
    private $logger;

    public function __construct($tunnelId, $internalId)
    {
        // no problem with avps:
        $this->id = $tunnelId;
        $this->internalId = $internalId;
        $this->logger = new Logger('server.log');
        $this->sessions = array();
    }

    /**
     * @param Packet $packet
     * @return Packet|null
     * @throws \Exception
     */
    public function processRequest(Packet $packet)
    {
        $messageType = NULL;
        if ($packet instanceof CtrlPacket) {
            $messageType = $packet->getAvp(AvpType::MESSAGE_TYPE_AVP)->value;
        }
        $sessionId = $packet->sessionId;
        switch ($messageType) {
            case MT_SCCRQ: // We've got a request, let's answer then? :-)
                $this->logger->info("[TUNNEL] Start-Control-Connection-Request");
                $responsePacket = $this->generateSCCRP();
                break;
            case MT_SCCCN:
                $this->logger->info("[TUNNEL] Start-Control-Connection-Connected");
                $responsePacket = $this->generateZLB();
                break;
            case MT_CDN:
                $this->logger->info("[TUNNEL] Call-Disconnect-Notify");
                unset($this->sessions[$sessionId]);
                $this->logger->info("[TUNNEL] Destroying session $sessionId");
                $result = $packet->getAVP(AvpType::RESULT_CODE_AVP)->value;
                $this->logger->info("Result code: $result[resultCode]");
                $this->logger->info("Error code: $result[errorCode]");
                return NULL; // Must not return anything on CDN
                break;
            case MT_HELLO:
                $this->logger->info("[TUNNEL] HELLO");
                $responsePacket = $this->generateZLB();
                break;
            default:
                if ($messageType == MT_ICRQ) {
                    $serverSessionId = count($this->sessions) + 1;
                    $sessionId = $packet->getAVP(AvpType::ASSIGNED_SESSION_ID_AVP)->value;
                    $this->sessions[$serverSessionId] = new Session($sessionId, $serverSessionId);
                }
                if (!$sessionId) {
                    var_dump($messageType);
                    throw new \Exception("Session not defined");
                }
                /* @var Session $session */
                $session = $this->sessions[$sessionId];
                $responsePacket = $session->processRequest($packet);
        }
        $responsePacket->setTunnelId($this->getId());
        return $responsePacket; // what do ween to return ? packet, Cap ;)
    }

    private function generateSCCRP()
    {
        $this->logger->info("[TUNNEL] Start-Control-Connection-Reply");
        $responsePacket = new CtrlPacket();
        // Add message type:
        $avp = AVPFactory::create(AvpType::MESSAGE_TYPE_AVP);
        $avp->setValue(MT_SCCRP);
        $responsePacket->addAVP($avp);
        // Add protocol version:
        $avp = AVPFactory::create(AvpType::PROTOCOL_VERSION_AVP);
        $avp->setValue(array('version' => Protocol::VERSION, 'revision' => Protocol::REVISION));
        $responsePacket->addAVP($avp);
        // Add framing capabilities:
        $avp = AVPFactory::create(AvpType::FRAMING_CAPABILITIES_AVP);
        $avp->setValue();
        $responsePacket->addAVP($avp);
        // Set host name:
        $avp = AVPFactory::create(AvpType::HOSTNAME_AVP);
        $avp->setValue('lomakov.net');
        $responsePacket->addAVP($avp);
        // Add tunnel id:
        $avp = AVPFactory::create(AvpType::ASSIGNED_TUNNEL_ID_AVP);
        $avp->setValue($this->internalId);
        $responsePacket->addAVP($avp);
        $avp = AVPFactory::create(AvpType::BEARER_CAPABILITIES_AVP);
        $avp->setValue(null);
        $responsePacket->addAVP($avp);
        $avp = AVPFactory::create(AvpType::FIRMWARE_REVISION_AVP);
        // TODO: set this to constant:
        $avp->setValue(1);
        $responsePacket->addAVP($avp);
        $avp = AVPFactory::create(AvpType::VENDOR_NAME_AVP);
        $avp->setValue(null);
        $responsePacket->addAVP($avp);
        $avp = AVPFactory::create(AvpType::RECEIVE_WINDOW_SIZE_AVP);
        // TODO: put this value into constant
        $avp->setValue(1024);
        $responsePacket->addAVP($avp);
        return $responsePacket;
    }

    public function getId()
    {
        return $this->id;
    }

    private function generateZLB()
    {
        $this->logger->info("[TUNNEL] ZLB ACK");
        $responsePacket = new CtrlPacket();
        return $responsePacket;
    }

}
