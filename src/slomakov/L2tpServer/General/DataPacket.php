<?php

namespace L2tpServer\General;

class DataPacket extends Packet
{
    protected $offset_size;
    protected $payload;

    protected function __construct()
    {
        parent::__construct();
        $this->packetType = self::TYPE_DATA;
        $this->isLengthPresent = 0;
        $this->isSequencePresent = 0;
        $this->isOffsetPresent = 0;
        $this->isPrioritized = 0;
        $this->numberSent = 0;
        $this->numberReceived = 0;
        $this->tunnelId = 0;
        $this->sessionId = 0;
    }

    public function create(Tunnel $tunnel, Session $session, $payload)
    {
        $this->setTunnelId($tunnel->getId());
        $this->setSessionId($session->getId());
        $this->setPayload($payload);
        return $this;
    }

    public function parse($rawData)
    {
        list(, $byte) = unpack('C', $rawData[0]);
        $this->parseHeader($rawData);
        if ($this->getType() != Packet::TYPE_DATA) {
            throw new \Exception("You're trying to parse not a data packet($byte)");
        }
        $this->payload = substr($rawData, $this->getHeaderLength());
        return $this; // What we need to return ?
    }

    public function encode()
    {
        $header = $this->encodeHeader(strlen($this->payload)); // encode header
        return $header . $this->payload;
    }

    // Return packet properties encoded as raw string:

    public function getPayload()
    {
        return $this->payload;
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    public function __toString()
    {
        $vars = get_object_vars($this);
        $vars['payload'] = strlen($vars['payload']) . ' bytes';
        return (new \ReflectionClass($this))->getShortName() . " '" . json_encode($vars). "'";
    }
}
