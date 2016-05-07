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
    protected $process;
    protected $pipes;

    public function __construct($sessionId, $internalId)
    {
        $this->logger = new Logger('server.log');
        $this->id = $sessionId;
        $this->internalId = $internalId;
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
        switch ($messageType) {
            case MT_ICRQ:
                $this->logger->info("[SESSION] Incoming-Call-Request");
                $serialNumber = $packet->getAVP(AvpType::CALL_SERIAL_NUMBER_AVP)->value;
                $this->logger->info("New session established with serial: $serialNumber");
                $responsePacket = $this->generateICRP();
                break;
            case MT_ICCN:
                $this->logger->info("[SESSION] Incoming-Call-Connected");
                //$this->startPPP();
                $responsePacket = $this->generateZLB();
                break;
            case NULL:
                $this->logger->info("[SESSION] Data packet");
                /* @var $packet DataPacket */
                //fwrite($this->pipes[1], $packet->payload);
                //$string  = fread($this->pipes[1], 0xFFFF);
                //var_dump($string);
                //die;
                return null;
                break;
            default:
                // ? Unknown state!
                throw new \Exception("Unknown message type $messageType");
        }
        $responsePacket->setSessionId($this->id);
        return $responsePacket;
    }

    protected function generateICRP()
    {
        $this->logger->info("[SESSION] Incoming-Call-Reply");
        $responsePacket = new CtrlPacket();
        // Add message type:
        $avp = AVPFactory::create(AvpType::MESSAGE_TYPE_AVP);
        $avp->setValue(MT_ICRP);
        $responsePacket->addAVP($avp);
        $avp = AVPFactory::create(AvpType::ASSIGNED_SESSION_ID_AVP);
        $avp->setValue($this->internalId);
        $responsePacket->addAVP($avp);
        return $responsePacket;
    }

    protected function startPPP()
    {
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin - канал, из которого дочерний процесс будет читать
            1 => array("pipe", "w"),  // stdout - канал, в который дочерний процесс будет записывать
            2 => array("file", "/tmp/error-output.txt", "a") // stderr - файл для записи
        );
        $cwd = '/tmp';
        $this->process = proc_open('/usr/sbin/pppd passive noauth nodetach debug notty', $descriptorspec, $this->pipes, $cwd);
        //sleep(1);
        if (!is_resource($this->process)) {
            // all bad!
            die('Cannot start pppd');
        } else {
            while (!feof($this->pipes[1])) {
                $string  = fread($this->pipes[1], 0xFFFF);
                var_dump($string);
            }
        }
    }

    private function generateZLB()
    {
        $this->logger->info("[TUNNEL] ZLB ACK");
        $responsePacket = new CtrlPacket();
        return $responsePacket;
    }
}
