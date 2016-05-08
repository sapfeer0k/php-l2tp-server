<?php

namespace L2tpServer\General;

abstract class Packet {

    const TYPE_CONTROL = 128,
        TYPE_DATA = 0;

	protected $isLengthPresent;
	protected $isSequencePresent;
	protected $isOffsetPresent;
	protected $isPrioritized;

	protected $protoVersion = 2; // always must be 2
	protected $packetType;

	protected $length;
	protected $tunnelId;
	protected $sessionId;
	protected $Ns;
	protected $Nr;
	protected $error;
    protected $offset;

	public function __construct($rawPacket=false) {
		if ($rawPacket) {
			if (!$this->parse($rawPacket)) {
				throw new \Exception("Can't parse packet");
			}
		}
	}

    public function getType()
    {
        return $this->packetType;
    }

	// Return packet properties encoded as raw string:
    public abstract function encode();

    //public abstract function getAVP($type);

    /**
     * @param $name name of property to return
     * @return mixed return any property data
     * @throws Exception
     */
    public function __get($name) {
		if (method_exists($this, ($method = 'get'.ucfirst($name)))) {
			return $this->$method;
		} else {
			if (property_exists($this, $name)) {
				return $this->$name;
			} else {
				throw new \Exception("You're trying to read property '$name' which doesn't exist");
			}
		}
	}

    /**
     * @param $packet
     * @return mixed
     */
    protected abstract function parse($packet);

    /**
     * @param string $packet - binary representation of the header
     * @return NULL
     * @throws \Exception
     */
    protected function parseHeader($packet)
    {
        list( , $byte) = unpack('C',$packet[0]);
        if (($byte & 128) == Packet::TYPE_CONTROL) {
            $this->packetType = Packet::TYPE_CONTROL;
        } else {
            $this->packetType = Packet::TYPE_DATA;
        }
        $this->isLengthPresent = ($byte & 64) ? 1 : 0;
        if ($this->isControl() && !$this->isLengthPresent) {
            throw new \Exception("Length field should be present for Control messages");
        }
        // bits 3,4 are ignored
        $this->isSequencePresent = ($byte & 8) ? 1 : 0;
        if ($this->isControl() && !$this->isSequencePresent) {
            throw new \Exception("Sequence fields should be present for Control messages");
        }
        $this->isOffsetPresent = ($byte & 2) ? 1 : 0;
        if ($this->isControl() && $this->isOffsetPresent) {
            throw new \Exception("Offset Size field should be 0 for Control messages");
        }
        $this->isPrioritized = ($byte & 1) ? 1 : 0;
        if ($this->isPrioritized && $this->isControl()) {
            throw new \Exception("Priority field should be 0 for Control messages");
        }
        unset($byte); // little cleanup
        list( , $byte2) = unpack('C',$packet[1]);
        $this->protoVersion = ($byte2 & 15 );
        if ($this->protoVersion != 2) {
            throw new \Exception("Unsupported protocol version {$this->protoVersion}");
        }
        $offset = 1;
        if ($this->isLengthPresent) { // actually it is always must be present, but double check :-)
            list( , $this->length) = unpack('n', $packet[++$offset].$packet[++$offset]);
        }
        list( , $this->tunnelId) = unpack('n', $packet[++$offset].$packet[++$offset]);
        list( , $this->sessionId) = unpack('n', $packet[++$offset].$packet[++$offset]);
        if ($this->isSequencePresent) { // actually it is always must be present, but double check :-)
            list( , $this->Ns) = unpack('n', $packet[++$offset].$packet[++$offset]);
            list( , $this->Nr) = unpack('n', $packet[++$offset].$packet[++$offset]);
        }
        if ($this->isData() && $this->isOffsetPresent) {
            $this->offset = unpack('n', $packet[++$offset].$packet[++$offset]);
        }
    }

    public function getHeaderLength()
    {
        return 6 + ($this->isLengthPresent ? 2 : 0) + ($this->isSequencePresent ? 4 : 0) + ($this->isOffsetPresent ? 2 + $this->offset : 0);
    }

    /**
     * @param $payloadSize
     * @return string
     */
    protected function formatHeader($payloadSize)
    {
        $header = '';
        $firstByte = 0;
        $firstByte += $this->packetType; // packet type
        $firstByte += $this->isLengthPresent * 64; // length present is true
        // skip 32, 16
        $firstByte += $this->isSequencePresent * 8; // sequence is present
        // skip 4
        $firstByte += $this->isOffsetPresent * 2;
        $firstByte += $this->isPrioritized;

        $header .= pack('C', $firstByte);
        $header .= pack('C', 2); // proto version
        if ($this->isLengthPresent) {
            $header .= pack('n', 0); // two bytes will be replaced by length
        }
        $header .= pack('n', (int)$this->tunnelId);
        $header .= pack('n', (int)$this->sessionId);
        if ($this->isSequencePresent) {
            $header .= pack('n', (int)$this->Ns);
            $header .= pack('n', (int)$this->Nr);
        }
        if ($this->isLengthPresent) {
            // Setting final length:
            $length = pack('n', $payloadSize + strlen($header));
            $header[2] = $length[0];
            $header[3] = $length[1];
        }
        return $header;
    }

    public function isControl()
    {
        return $this->getType() == self::TYPE_CONTROL;
    }

    public function isData()
    {
        return $this->getType() == self::TYPE_DATA;
    }

    public function setTunnelId($tunnelId)
    {
        $this->tunnelId = $tunnelId;
    }

    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }
}


