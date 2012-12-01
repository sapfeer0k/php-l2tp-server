<?php
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


class l2tp_ctrl_packet extends l2tp_packet {

	protected $message_type;
	protected $avps;

	function __construct($raw_packet=false) {
		if ($raw_packet) {
			if (!$this->parse($raw_packet)) {
				throw new Exception("Can't parse packet");
			}
		}
	}

	protected function parse($packet) {
		list( , $byte) = unpack('C',$packet[0]);
		if (($byte & 128) == PACKET_TYPE_CONTROL) {
			$this->packet_type = PACKET_TYPE_CONTROL;
		} else {
			throw new Exception("You're trying to parse not a control packet");
		}

		$this->is_length_present = ($byte & 64) ? true : false;
		if (!$this->is_length_present) {
			throw new Exception("Length field should be present for Control messages");
		}
		// bits 3,4 are ignored
		$this->is_sequence_present = ($byte & 8) ? true : false;
		if (!$this->is_sequence_present) {
			throw new Exception("Sequence fields should be present for Control messages");
		}
		$this->is_offset_present = ($byte & 2) ? true : false;
		if ($this->is_offset_present) {
			throw new Exception("Offset Size field should be 0 for Control messages");
		}
		$this->is_prioritized = ($byte & 1) ? true : false;
		if ($this->is_prioritized) {
			throw new Exception("Priority field should be 0 for Control messages");
		}
		unset($byte);
		list( , $byte2) = unpack('C',$packet[1]);
		$this->proto_version = ($byte2 & 15 );
		if ($this->proto_version != 2) {
			throw new Exception("Unsupported protocol version {$this->proto_version}");
		}
		if ($this->is_length_present) {
			list( , $this->length) = unpack('n', $packet[2].$packet[3]);
		}
		list( , $this->tunnel_id) = unpack('n', $packet[4].$packet[5]);
		list( , $this->session_id) = unpack('n', $packet[6].$packet[7]);
		if ($this->is_sequence_present) {
			list( , $this->Ns) = unpack('n', $packet[8].$packet[9]);
			list( , $this->Nr) = unpack('n', $packet[10].$packet[11]);
		}
		// Further we'll work with $packet_data property:
		$packet_data = substr($packet, 12);
		// let's parse packet's AVPs:
		while(strlen($packet_data)) {
			// Get AVP length:
			list( , $avp_bytes) = unpack('n', $packet_data[0].$packet_data[1]);
			$avp_len = $avp_bytes & 1023;
			$avp_raw_data = substr($packet_data, 0, $avp_len);

			$this->avps[] = factory::parseAVP($avp_raw_data);
			$packet_data = substr($packet_data, $avp_len);

			unset($avp_len, $avp_bytes);
		}
		if ($this->avps[0]->type == MESSAGE_TYPE_AVP) {
			$this->message_type = $this->avps[0]->value;
		} else {
			throw new Exception("Message type AVP not found in the packet");
		}

		if ($this->getAVP(PROTOCOL_VERSION_AVP) && $this->getAVP(HOSTNAME_AVP) && $this->getAVP(FRAMING_CAPABILITIES_AVP) && $this->getAVP(ASSIGNED_TUNNEL_ID_AVP)) {

		} else {
			throw new Exception("Not all AVP are presented in the packet");
		}
		return true;
	}

	public function getAVP($type) {
		foreach($this->avps as $id => $avp) {
			if ($avp->type == $type) {
				return $avp;
			}
		}
		return NULL;
	}

	// Return packet properties encoded as raw string:
	function encode() {
		return ;
	}

}


?>
