<?php

namespace L2tpServer\General;


class DataPacket extends Packet {

	protected $offset_size;
    protected $payload;

	public function __construct($rawPacket=false) {
		if ($rawPacket) {
			if (!$this->parse($rawPacket)) {
				throw new \Exception("Can't parse packet");
			}
        } else {
             $this->packetType = self::TYPE_DATA;
             $this->isLengthPresent = 0;
             $this->isSequencePresent = 0;
             $this->isOffsetPresent = 0;
             $this->isPrioritized = 0;
             $this->Ns = 0;
             $this->Nr = 0;
             $this->tunnelId = 0;
             $this->sessionId = 0; 
        }
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

	public function parse($packet) {
        list( , $byte) = unpack('C',$packet[0]);
        //var_dump("Raw packet data: " . bin2hex($packet));
        $this->parseHeader($packet);
		if ($this->getType() != Packet::TYPE_DATA) {
			throw new \Exception("You're trying to parse not a data packet($byte)");
		}
        $this->payload = substr($packet, $this->getHeaderLength());
		return true; // What we need to return ?
	}

	// Return packet properties encoded as raw string:
	public function encode() {
        $header = $this->encodeHeader(strlen($this->payload)); // encode header
        return $header . $this->payload;
	}

}


?>
