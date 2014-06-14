<?php

namespace L2tpServer\General;

use L2tpServer\AVPs\AVPFactory;
use L2tpServer\Constants\AvpType;
use L2tpServer\Constants\SessionState;
use Packfire\Logger\File as Logger;

class Session {

    protected $id;
    protected $internalId;
    protected $logger;

    public function __construct($sessionId, $internalId)
    {
        $this->logger = new Logger('server.log');
        $this->id = $sessionId;
        $this->internalId = $internalId;
    }

    /**
     * @param CtrlPacket $packet
     * @return CtrlPacket|null
     * @throws \Exception
     */
    public function processRequest(CtrlPacket $packet)
    {
        $messageType = $packet->getAvp(AvpType::MESSAGE_TYPE_AVP)->value;
        switch ($messageType) {
            case MT_ICRQ:
                $this->logger->info("[SESSION] Incoming-Call-Request");
                $serialNumber = $packet->getAVP(AvpType::CALL_SERIAL_NUMBER_AVP)->value;
                $this->logger->info("New session established $serialNumber");
                $responsePacket = $this->generateICRP();
                break;
            case MT_ICCN:
                $this->logger->info("[SESSION] Incoming-Call-Connected");
                var_dump($packet->getAVP(AvpType::PROXY_AUTHEN_TYPE_AVP)->value);
                $responsePacket = $this->generateZLB();
                break;
            default:
                // ? Unknown state!
                throw new \Exception("Unknown packet type");
        }
        $responsePacket->setSessionId($this->id);
        return $responsePacket;
    }

    protected function generateICRP()
    {
        $this->logger->info("[SESSION] Incoming-Call-Reply");
        $responsePacket = new CtrlPacket();
        // Add message type:
        $avp = AVPFactory::createAVP(AvpType::MESSAGE_TYPE_AVP);
        $avp->setValue(MT_ICRP);
        $responsePacket->addAVP($avp);
        $avp = AVPFactory::createAVP(AvpType::ASSIGNED_SESSION_ID_AVP);
        $avp->setValue($this->internalId);
        $responsePacket->addAVP($avp);
        return $responsePacket;
    }

    private function generateZLB()
    {
        $this->logger->info("[TUNNEL] ZLB ACK");
        $responsePacket = new CtrlPacket();
        return $responsePacket;
    }
}
