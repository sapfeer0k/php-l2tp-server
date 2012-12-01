<?php

define('PACKET_TYPE_CONTROL', 128);
define('PACKET_TYPE_DATA', 0);


abstract class l2tp_packet {

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
	protected $offset_size;
	protected $raw_data;
	protected $error;
	protected $avps;

	function __construct($raw_packet=false) {
		if ($raw_packet) {
			if (!$this->parse($raw_packet)) {
				throw new Exception("Can't parse packet");	
			}
		}
	}

	protected abstract function parse($packet);

	public function getAVP($type) {
		foreach($this->avps as $id => $avp) {
			if ($avp->type == $type) {
				return $avp;
			}
		}
		return false;
	} 

	// Return packet properties encoded as raw string:
	abstract function encode();

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
