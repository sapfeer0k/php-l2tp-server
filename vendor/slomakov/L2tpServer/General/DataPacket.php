<?php

namespace L2tpServer\General;


class DataPacket extends Packet {

	protected $offset_size;

	public function __construct($rawPacket=false) {
		if ($rawPacket) {
			if (!$this->parse($rawPacket)) {
				throw new Exception("Can't parse packet");
			}
		}
	}

	public function parse($packet) {
		trigger_error("Method ".__METHOD__." in class ".__CLASS__." hasn't finished");
		list( , $byte) = unpack('C',$packet[0]);
		if ($byte & 128 == Packet::TYPE_DATA) {
			$this->packetType = Packet::TYPE_DATA;
		} else {
			throw new \Exception("You're trying to parse not a data packet($byte)");
		}
/*
		list( , $byte) = unpack('C',$packet[0]);
		$this->packetType = ( $byte & 128 ) ? Packet::TYPE_CONTROL : MESSAGE_TYPE_DATA ;
		$this->is_length_present = ($byte & 64) ? true : false;
		if ($this->packetType == Packet::TYPE_CONTROL && !$this->is_length_present) {
			throw new Exceptions("Length field should be present for Control messages");
		}
		// bits 3,4 are ignored
		$this->is_sequence_present = ($byte & 8) ? true : false;
		if ($this->packetType == Packet::TYPE_CONTROL && !$this->is_sequence_present) {
			throw new Exceptions("Length field should be present for Control messages");
		}
		$this->is_offset_present = ($byte & 2) ? true : false;
		if ($this->packetType == Packet::TYPE_CONTROL && $this->is_offset_present) {
			throw new Exceptions("Offset Size field should be 0 for Control messages");
		}
		$this->is_prioritized = ($byte & 1) ? true : false;
		if ($this->packetType == Packet::TYPE_CONTROL && $this->is_prioritized) {
			throw new Exceptions("Priority field should be 0 for Control messages");
		}
		unset($byte);
		list( , $byte2) = unpack('C',$packet[1]);
		$this->protoVersion = ($byte2 & 15 );
		if ($this->protoVersion != 2) {
			throw new Exceptions("Unsupported protocol version {$this->protoVersion}");
		}
		if ($this->is_length_present) {
			list( , $this->length) = unpack('n', $packet[2].$packet[3]);
		}
		list( , $this->tunnel_id) = unpack('n', $packet[4].$packet[5]);
		list( , $this->sessionId) = unpack('n', $packet[6].$packet[7]);
		if ($this->is_sequence_present) {
			list( , $this->Ns) = unpack('n', $packet[8].$packet[9]);
			list( , $this->Nr) = unpack('n', $packet[10].$packet[11]);
		}
		if ($this->is_offset_present) {
			list( , $this->offset_size) = unpack('n', $packet[12].$packet[13]);
			$packet_data = substr($packet, (14 + $this->offset_size));
		} else {
			$packet_data = substr($packet, 12);
		}
		// Further we'll work with $packet_data property:
		// let's find out AVPs length and parse it with AVPs class:
		while(strlen($packet_data)) {
			list( , $avp_len) = unpack('n', $packet_data[0].$packet_data[1]);
			$avp_len = $avp_len & 1023;
			$this->avps[] = new l2tp_avp(substr($packet_data, 0, $avp_len));
			$packet_data = substr($packet_data, $avp_len);
			unset($avp_len);
		}
*/
		return true; // What we need to return ?
	}

	public function getAVP($type) {
		foreach($this->avps as $avp) {
			if ($avp->type == $type) {
				return $avp;
			}
		}
		return false;
	}

	// Return packet properties encoded as raw string:
	function encode() {
		return ;
	}

}


?>
