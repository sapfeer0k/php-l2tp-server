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
use Packfire\Logger\File as Logger;

class CtrlPacket extends Packet {

    const TRUE = 1;
    const FALSE = 0;

	protected $avps = array();

    public function __construct($rawPacket=false) {
        $this->logger = new Logger('server.log');
		if ($rawPacket) {
			if (!$this->parse($rawPacket)) {
				throw new \Exception("Can't parse packet");
			}
		} else {
            $this->packetType = self::TYPE_CONTROL;
            $this->isLengthPresent = self::TRUE;
            $this->isSequencePresent = self::TRUE;
            $this->isOffsetPresent = self::FALSE;
            $this->isPrioritized = self::FALSE;
            $this->Ns = 0;
            $this->Nr = 0;
            $this->tunnelId = 0;
            $this->sessionId = 0;
        }
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
        if (strlen($payload)) {
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
                $this->avps[] = AVPFactory::create(array('avp_raw_data' => $avp_raw_data));
            } catch (IgnoreAVPException $e) {
                trigger_error($e->getMessage());
            }
            $payload = substr($payload, $avp_len);

            unset($avp_len, $avp_bytes);
        }
        if ($this->avps[0]->type != AvpType::MESSAGE_TYPE_AVP) {
            throw new TunnelException("Message type AVP not found in the packet");
            //$this->message_type = $this->avps[0]->value;
        }
    }

	// Return packet properties encoded as raw string:
	function encode() {
        $packetData = '';
        foreach($this->avps as $avp) { // encode AVP
            /* @var $avp BaseAVP */
            $packetData .= $avp->encode();
        }
        $header = $this->formatHeader(strlen($packetData)); // encode header
        $this->length = strlen($header . $packetData);
		return $header . $packetData;
	}

    public function setNs($nS)
    {
        if (!is_numeric($nS) && $nS < 0) {
            throw new \Exception("Nr must be greater or euqual than 0");
        }
        $this->Ns = $nS % 65536;
    }

    public function setNr($nR)
    {
        if (!is_numeric($nR) && $nR < 0) {
            throw new \Exception("Nr must be greater or euqual than 0");
        }
        $this->Nr = $nR % 65536;
    }

    public static function create()
    {
        return new self();
    }

    /**
     * @param $type
     * @return BaseAVP
     */
    public function getAVP($type)
    {
        foreach($this->avps as $avp) {
            if ($avp->type == $type) {
                return $avp;
            }
        }
        return NULL;
    }

    public function getAVPS()
    {
        // DEBUG METHOD ONLY
        return $this->avps;
    }

}


?>
