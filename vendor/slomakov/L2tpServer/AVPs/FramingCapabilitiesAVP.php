<?php

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType;
use L2tpServer\Exceptions\AVPException;

class FramingCapabilitiesAVP extends BaseAVP 
{
    public function __construct($isHidden=false)
    {
        $this->is_hidden = $isHidden;
        $this->is_mandatory = 1;
        $this->type = AvpType::FRAMING_CAPABILITIES_AVP;
		$this->value = array("sync" => 1, "async" => 0 ); // readonly value!
    }

    public static function import($data) {
        $avp = new self();
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$avp->is_mandatory = ($avp_flags_len & 32768) ? 1 : 0;
		$avp->is_hidden = ($avp_flags_len & 16384) ? 1 : 0;
		$avp->length = ($avp_flags_len & 1023);
		if (!$avp->is_hidden && $avp->length != 10) {
			throw new AVPException("Invalid length for Framing Capabilities AVP!");
		}
		list( , $avp->vendor_id) = unpack('n', $data[2].$data[3]);
		list( , $avp->type) = unpack('n', $data[4].$data[5]);
		$avp->value = array();
		list( , $flag_byte) = unpack('C', $data[9]);

		$avp->value["async"] = ($flag_byte & 2) ? 1 : 0;
		$avp->value["sync"] = ($flag_byte & 1) ? 1 : 0;
		$avp->validate();
        return $avp;
	}

	public function setValue($value=NULL) {
		// this value is readonly!
        return true;
	}

	public function validate() {
			if (!$this->value['sync']) {
				throw new AVPException("No available Framing Capabilites for this connection!");
			}
    }

    protected function getEncodedValue()
    {
        $value = $this->value['async'] * 2 + $this->value['sync'];
        $value = pack('nn', 0, $value);
        return $value;
    }

}
