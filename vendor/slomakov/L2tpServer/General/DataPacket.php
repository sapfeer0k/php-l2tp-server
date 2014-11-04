<?php

namespace L2tpServer\General;


class DataPacket extends Packet {

	protected $offset_size;
    protected $payload;

	public function __construct($rawPacket=false) {
		if ($rawPacket) {
			if (!$this->parse($rawPacket)) {
				throw new Exception("Can't parse packet");
			}
		}
	}

	public function parse($packet) {
		list( , $byte) = unpack('C',$packet[0]);
        $this->parseHeader($packet);
		if ($this->getType() != Packet::TYPE_DATA) {
			throw new \Exception("You're trying to parse not a data packet($byte)");
		}
        $this->payload = substr($packet, $this->getHeaderLength());
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
