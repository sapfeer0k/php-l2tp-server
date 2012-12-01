<?php


class l2tp_inf_packet extends l2tp_packet {

	function __construct($raw_packet=false) {
		if ($raw_packet) {
			if (!$this->parse($raw_packet)) {
				throw new Exception("Can't parse packet");	
			}
		}
	}

	private function parse($packet) {
		trigger_error("Warning Method ".__METHOD__." in class ".__CLASS__." hasn't finished")
		if ($byte & 128 == PACKET_TYPE_DATA) {
			$this->packet_type = PACKET_TYPE_DATA;
		} else {
			throw new Exception("You're trying to parse not a data packet");
		}
/*
		list( , $byte) = unpack('C',$packet[0]);
		$this->packet_type = ( $byte & 128 ) ? PACKET_TYPE_CONTROL : MESSAGE_TYPE_DATA ;
		$this->is_length_present = ($byte & 64) ? true : false;
		if ($this->packet_type == PACKET_TYPE_CONTROL && !$this->is_length_present) {
			throw new Exception("Length field should be present for Control messages");
		}
		// bits 3,4 are ignored 
		$this->is_sequence_present = ($byte & 8) ? true : false;
		if ($this->packet_type == PACKET_TYPE_CONTROL && !$this->is_sequence_present) {
			throw new Exception("Length field should be present for Control messages");
		}
		$this->is_offset_present = ($byte & 2) ? true : false;
		if ($this->packet_type == PACKET_TYPE_CONTROL && $this->is_offset_present) {
			throw new Exception("Offset Size field should be 0 for Control messages");
		}
		$this->is_prioritized = ($byte & 1) ? true : false;
		if ($this->packet_type == PACKET_TYPE_CONTROL && $this->is_prioritized) {
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
		if ($this->is_offset_present) {
			list( , $this->offset_size) = unpack('n', $packet[12].$packet[13]);
			$packet_data = substr($packet, (14 + $this->offset_size));
		} else {
			$packet_data = substr($packet, 12);
		}
		// Further we'll work with $packet_data property:
		// let's find out AVP length and parse it with AVP class:
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
		foreach($this->avps as $id => $avp) {
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

	function __get($name) {
		if (method_exists($this, ($method = 'get'.ucfirst($name)))) {
			return $this->$method;
		} else {
			if (property_exists($this, $name)) {
				return $this->$name;
			} else {
				throw new Exception("You're trying to read property '$name' which doesn't exist");
			}
		}
	}

	function __set($name, $value) {
		if (method_exists($this, ($method = 'set'.ucfirst($name)))) {
			return $this->$method;
		} else {
			if (property_exists($this, $name)) {
				$this->$name = $value;
			} else {
				throw new Exception("You're trying to change property '$name' which doesn't exist");
			}
		}
	}
}


?>
