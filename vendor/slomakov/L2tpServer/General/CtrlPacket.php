<?php

namespace L2tpServer\General;

// Control connection managment
// 0 is reserved
define('MT_SCCRQ', 1);
define('MT_SCCRP', 2);
define('MT_SCCCN', 3);
define('MT_StopCCN', 4);
// 5 is reserved
define('MT_HELLO', 6);

// Call managment:
define('MT_OCRQ' , 7);
define('MT_OCRP' , 8);
define('MT_OCCN' , 9);
define('MT_ICRQ' , 10);
define('MT_ICRP' , 11);
define('MT_ICCN' , 12);
// 13 is reserved
define('MT_CDN' , 14);
// Error Reporting
define('MT_WEN', 15);
// PPP Session Control
define('MT_SLI', 16);

use L2tpServer\AVPs\AVPFactory,
    L2tpServer\Constants\AvpType,
    L2tpServer\Exceptions\TunnelException,
    L2tpServer\Exceptions\IgnoreAVPException,
    L2tpServer\AVPs\BaseAVP;

class CtrlPacket extends Packet {

    const TRUE = 1;
    const FALSE = 0;

	protected $message_type;
	protected $avps = array();

	public function __construct($rawPacket=false) {
		if ($rawPacket) {
			if (!$this->parse($rawPacket)) {
				throw new \Exception("Can't parse packet");
			}
            return true;
		}
        $this->packetType = self::TYPE_CONTROL;
        $this->isLengthPresent = self::TRUE;
        $this->isSequencePresent = self::TRUE;
        $this->isOffsetPresent = self::FALSE;
        $this->isPrioritized = self::FALSE;
        $this->Ns = 0;
        $this->Nr = 0;
        $this->tunnelId = 0;
        $this->sessionId = 0;
        return true;
	}

    public function addAVP(BaseAVP $avp)
    {
        array_push($this->avps, $avp);
        return true;
    }

	protected function parse($packet) {
        $this->parseHeader($packet);
		// Further we'll work with $packet_data property:
		$payload = substr($packet, 12);
        if (mb_strlen($payload)) {
            $this->parseAVPs($payload);
        }
		return true;
	}

    protected function parseAVPs($payload)
    {
        // let's parse packet's AVPs:
        while(strlen($payload)) {
            // Get AVPs length:
            list( , $avp_bytes) = unpack('n', $payload[0].$payload[1]);
            $avp_len = $avp_bytes & 1023;
            $avp_raw_data = substr($payload, 0, $avp_len);
            try {
                $this->avps[] = AVPFactory::createAVP(array('avp_raw_data' => $avp_raw_data));
            } catch (IgnoreAVPException $e) {
                trigger_error($e->getMessage());
            }
            $payload = substr($payload, $avp_len);

            unset($avp_len, $avp_bytes);
        }
        if ($this->avps[0]->type == AvpType::MESSAGE_TYPE_AVP) {
            $this->message_type = $this->avps[0]->value;
        } else {
            throw new TunnelException("Message type AVP not found in the packet");
        }
    }

	// Return packet properties encoded as raw string:
	function encode() {
        $packetData = '';
        foreach($this->avps as $avp) { // encode AVP
            /* @var $avp BaseAVP */
            $packetData .= $avp->encode();
        }
        $header = $this->formatHeader(mb_strlen($packetData)); // encode header
        $this->length = mb_strlen($header . $packetData);
		return $header . $packetData;
	}

    public function setNs($nS)
    {
        if (!is_numeric($nS) && $nS < 0) {
            throw new \Exception("Nr must be greater or euqual than 0");
        }
        $this->Ns = $nS;
    }

    public function setNr($nR)
    {
        if (!is_numeric($nR) && $nR < 0) {
            throw new \Exception("Nr must be greater or euqual than 0");
        }
        $this->Nr = $nR;
    }

    public function setTunnelId($tunnelId)
    {
        $this->tunnelId = $tunnelId;
    }

    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public static function create()
    {
        return new self();
    }

    public function getAVP($type) {
        foreach($this->avps as $avp) {
            if ($avp->type == $type) {
                return $avp;
            }
        }
        return NULL;
    }

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

        $header .= chr($firstByte);
        $header .= chr(2); // proto version
        $header .= pack('n', 0); // two bytes will be replaced by length
        $header .= pack('n', (int)$this->tunnelId);
        $header .= pack('n', (int)$this->sessionId);
        $header .= pack('n', (int)$this->Ns);
        $header .= pack('n', (int)$this->Nr);
        // Setting final length:
        $length = pack('n', $payloadSize + mb_strlen($header));
        $header[2] = $length[0];
        $header[3] = $length[1];
        return $header;
    }

    /**
     * @param string $packet - binary representation of the header
     * @return NULL
     * @throws \Exception
     */
    protected function parseHeader($packet)
    {
        list( , $byte) = unpack('C',$packet[0]);
        //var_dump(decbin($byte));
        //die('there');
        if (($byte & 128) == Packet::TYPE_CONTROL) {
            $this->packetType = Packet::TYPE_CONTROL;
        } else {
            throw new \Exception("You're trying to parse not a control packet");
        }
        $this->isLengthPresent = ($byte & 64) ? self::TRUE : self::FALSE;
        if (!$this->isLengthPresent) {
            throw new \Exception("Length field should be present for Control messages");
        }
        // bits 3,4 are ignored
        $this->isSequencePresent = ($byte & 8) ? self::TRUE : self::FALSE;
        if (!$this->isSequencePresent) {
            throw new \Exception("Sequence fields should be present for Control messages");
        }
        $this->isOffsetPresent = ($byte & 2) ? self::TRUE : self::FALSE;
        if ($this->isOffsetPresent) {
            throw new \Exception("Offset Size field should be 0 for Control messages");
        }
        $this->isPrioritized = ($byte & 1) ? self::TRUE : self::FALSE;
        if ($this->isPrioritized && $this->packetType == Packet::TYPE_CONTROL) {
            throw new \Exception("Priority field should be 0 for Control messages");
        }
        unset($byte); // little cleanup
        list( , $byte2) = unpack('C',$packet[1]);
        $this->protoVersion = ($byte2 & 15 );
        if ($this->protoVersion != 2) {
            throw new \Exception("Unsupported protocol version {$this->protoVersion}");
        }
        if ($this->isLengthPresent) { // actually it is always must be present, but double check :-)
            list( , $this->length) = unpack('n', $packet[2].$packet[3]);
        }
        list( , $this->tunnelId) = (int) array_shift(unpack('n', $packet[4].$packet[5]));
        list( , $this->sessionId) = unpack('n', $packet[6].$packet[7]);
        if ($this->isSequencePresent) { // actually it is always must be present, but double check :-)
            list( , $this->Ns) = unpack('n', $packet[8].$packet[9]);
            list( , $this->Nr) = unpack('n', $packet[10].$packet[11]);
        }
    }

}


?>
