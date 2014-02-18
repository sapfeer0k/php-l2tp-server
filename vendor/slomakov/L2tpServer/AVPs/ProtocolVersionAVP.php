<?php

namespace L2tpServer\AVPs;

use L2tpServer\Constants\Protocol,
    L2tpServer\Exceptions\AVPException;

class ProtocolVersionAVP extends BaseAVP {

	public function setValue($value) {
		 if (is_array($value) && isset($value['version']) && isset($value['revision'])) {
			$this->value = $value;
		 } else {
			 throw new AVPException("Invalid value for protocol type AVP");
		 }
		 return true;
	}

	public function encode() {
		throw new AVPException("Encode method isn't defined. Please, implement me...");
	}

	protected function validate() {
		if ($this->is_hidden) {
			throw new AVPException("Protocol version AVP must not be HIDDEN");
		}
		if ($this->value['version'] != Protocol::VERSION || $this->value['revision'] != Protocol::REVISION) {
			throw new AVPException("Protocol version doesn't supported");
		}
	}

    protected function parse($data) {
        list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
        $this->is_mandatory = ($avp_flags_len & 32768) ? true : false;
        $this->is_hidden = ($avp_flags_len & 16384) ? true : false;
        $this->length = ($avp_flags_len & 1023);
        if ($this->length != 8 ) {
            throw new AVPException("Invalid length for protocol version AVP!");
        }
        list( , $this->vendor_id) = unpack('n', $data[2].$data[3]);
        list( , $this->type) = unpack('n', $data[4].$data[5]);
        $this->value = array() ;
        list( , $this->value["version"]) = unpack('C', $data[6]);
        list( , $this->value["revision"]) = unpack('C', $data[7]);
        $this->validate();
    }


}
