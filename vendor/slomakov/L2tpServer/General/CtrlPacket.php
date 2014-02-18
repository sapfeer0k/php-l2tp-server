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

	protected $message_type;
	protected $avps = array();

	public function __construct($raw_packet=false) {
		if ($raw_packet) {
			if (!$this->parse($raw_packet)) {
				throw new \Exception("Can't parse packet");
			}
		}
	}

    public function addAVP(BaseAVP $avp)
    {
        array_push($this->avps, $avp);
        return true;
    }

	protected function parse($packet) {
        $this->parseHeader($packet);
		// Further we'll work with $packet_data property:
		$packet_data = substr($packet, 12);
		// let's parse packet's AVPs:
		while(strlen($packet_data)) {
			// Get AVPs length:
			list( , $avp_bytes) = unpack('n', $packet_data[0].$packet_data[1]);
			$avp_len = $avp_bytes & 1023;
			$avp_raw_data = substr($packet_data, 0, $avp_len);

			try {
				$this->avps[] = AVPFactory::createAVP(array('avp_raw_data' => $avp_raw_data));
			} catch (IgnoreAVPException $e) {
				trigger_error($e->getMessage());
			}
			$packet_data = substr($packet_data, $avp_len);

			unset($avp_len, $avp_bytes);
		}
		if ($this->avps[0]->type == AvpType::MESSAGE_TYPE_AVP) {
			$this->message_type = $this->avps[0]->value;
		} else {
			throw new TunnelException("Message type AVP not found in the packet");
		}
/*
		if ($this->getAVP(PROTOCOL_VERSION_AVP) && $this->getAVP(HOSTNAME_AVP) && $this->getAVP(FRAMING_CAPABILITIES_AVP) && $this->getAVP(ASSIGNED_TUNNEL_ID_AVP)) {

		} else {
			throw new Exceptions("Not all AVPs are present in the packet");
		}
*/
		return true;
	}

	// Return packet properties encoded as raw string:
	function encode() {
		return ;
	}

    public static function create($type, $nS, $nR, $tunnelId = NULL, $sessionId = NULL)
    {
        if ($type != self::TYPE_CONTROL && $type != self::TYPE_DATA) {
            throw new \Exception("Unknown packet type");
        }
        $packet = new self();
        $packet->packet_type = $type;
        $packet->Ns = $nS;
        $packet->Nr = $nR;
        if ($tunnelId !== NULL) {
            $packet->tunnel_id = $tunnelId;
        }
        if ($sessionId !== NULL) {
            $packet->session_id = $sessionId;
        }
    }

}


?>
