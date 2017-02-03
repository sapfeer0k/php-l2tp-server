<?php

namespace L2tpServer\General;

use L2tpServer\AVPs\AVPFactory;
use L2tpServer\Constants\AvpType;
use L2tpServer\Constants\SessionState;
use L2tpServer\PacketFactory;
use L2tpServer\Tools\TLogger;
use Packfire\Logger\File as Logger;

class Session
{
    use TLogger;
    
    protected $id;
    protected $internalId;
    protected $process;
    protected $pipes;

    public function __construct($sessionId, $internalId)
    {
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
        $messageType = null;
        if ($packet instanceof CtrlPacket) {
            $messageType = $packet->getAVP(AvpType::MESSAGE_TYPE_AVP)->value;
        }
        $responsePacket = null;
        switch ($messageType) {
            case MT_ICRQ:
                $this->getLogger()->info("[SESSION] Incoming-Call-Request");
                $serialNumber = $packet->getAVP(AvpType::CALL_SERIAL_NUMBER_AVP)->value;
                $this->getLogger()->info("New session established with serial: $serialNumber");
                $responsePacket = $this->generateICRP();
                break;
            case MT_ICCN:
                $this->getLogger()->info("[SESSION] Incoming-Call-Connected");
                $responsePacket = PacketFactory::generateZLB();
                break;
            case null:
                //$this->getLogger()->info("[SESSION] Data packet");
                /* @var $packet DataPacket */
                $this->startPPP();
                $ppp = new PppFrameParser();
                $frame = $ppp->encode($packet->getPayload());
                fwrite($this->pipes[0], $frame);
                break;
            default:
                // ? Unknown state!
                throw new \Exception("Unknown message type $messageType");
        }
        if ($responsePacket) {
            $responsePacket->setSessionId($this->id);
        }
        return $responsePacket;
    }

    protected function generateICRP()
    {
        $this->getLogger()->info("[SESSION] Incoming-Call-Reply");
        $responsePacket = CtrlPacket::factory();
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
        if (is_resource($this->process)) {
            $status = proc_get_status($this->process);
            if (!empty($status) && $status['running']) {
                return ;
            }
        }
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin - read pipe
            1 => array("pipe", "w"),  // stdout - write pipe
            2 => array("pipe", "a") // stderr - error pipe
        );
        $command = '/usr/sbin/pppd logfile /var/log/ppp.log notty file /etc/ppp/options.xl2tpd';
        $this->process = proc_open($command, $descriptorspec, $this->pipes);
        if (!is_resource($this->process)) {
            // all bad!
            die('Cannot start pppd');
        } else {
            foreach ($this->pipes as $pipe) {
                stream_set_blocking($pipe, 0);
            }
        }
    }

    public function getOutputPipe()
    {
        return $this->pipes[1];
    }

    public function getId()
    {
        return $this->id;
    }
}
