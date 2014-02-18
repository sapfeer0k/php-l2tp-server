<?php

namespace L2tpServer\General;

abstract class Packet {

    const TYPE_CONTROL = 128,
        TYPE_DATA = 0;

	protected $is_length_present;
	protected $is_sequence_present;
	protected $is_offset_present;
	protected $is_prioritized;

	protected $proto_version;
	protected $packet_type;

	protected $length;
	protected $tunnel_id;
	protected $session_id;
	protected $Ns;
	protected $Nr;
	protected $raw_data;
	protected $error;

	public function __construct($raw_packet=false) {
		if ($raw_packet) {
			if (!$this->parse($raw_packet)) {
				throw new \Exception("Can't parse packet");
			}
		}
	}
	// Decode packet from raw data
	protected abstract function parse($packet);
	// Return packet properties encoded as raw string:
	abstract public function encode();

    protected function parseHeader($packet)
    {
        list( , $byte) = unpack('C',$packet[0]);
        if (($byte & 128) == Packet::TYPE_CONTROL) {
            $this->packet_type = Packet::TYPE_CONTROL;
        } else {
            throw new \Exception("You're trying to parse not a control packet");
        }
        $this->is_length_present = ($byte & 64) ? true : false;
        if (!$this->is_length_present && $this->packet_type == Packet::TYPE_CONTROL) {
            throw new \Exception("Length field should be present for Control messages");
        }
        // bits 3,4 are ignored
        $this->is_sequence_present = ($byte & 8) ? true : false;
        if (!$this->is_sequence_present && $this->packet_type == Packet::TYPE_CONTROL) {
            throw new \Exception("Sequence fields should be present for Control messages");
        }
        $this->is_offset_present = ($byte & 2) ? true : false;
        if ($this->is_offset_present && $this->packet_type == Packet::TYPE_CONTROL) {
            throw new \Exception("Offset Size field should be 0 for Control messages");
        }
        $this->is_prioritized = ($byte & 1) ? true : false;
        if ($this->is_prioritized && $this->packet_type == Packet::TYPE_CONTROL) {
            throw new \Exception("Priority field should be 0 for Control messages");
        }
        unset($byte);
        list( , $byte2) = unpack('C',$packet[1]);
        $this->proto_version = ($byte2 & 15 );
        if ($this->proto_version != 2) {
            throw new \Exception("Unsupported protocol version {$this->proto_version}");
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
    }

    protected function formatHeader()
    {
        $header = '';
        $firstByte = 0;
        $firstByte |= $this->packet_type;
        if ($this->packet_type == self::TYPE_CONTROL) {
            $firstByte |= 64;
        }
        if ($this->packet_type == self::TYPE_CONTROL) {
            $firstByte |= 8;
        }
        if ($this->packet_type != self::TYPE_CONTROL) {
            $firstByte |= 2;
        }
        if ($this->packet_type != self::TYPE_CONTROL) {
            $firstByte |= 1;
        }
        $header .= pack('C', $firstByte);
        $header .= pack('C', 2); // proto version

        return $header;
    }

	public function __get($name) {
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
/*
	public function __set($name, $value) {
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
*/
    public function getAVP($type) {
        foreach($this->avps as $avp) {
            if ($avp->type == $type) {
                return $avp;
            }
        }
        return NULL;
    }
}


?>