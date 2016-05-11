<?php

namespace L2tpServer\AVPs;

use L2tpServer\Exceptions\AVPException;
use L2tpServer\Constants\AvpType;

class CallSerialNumberAVP extends BaseAVP
{
    public function __construct($is_hidden=false)
    {
        $this->is_mandatory = 1;
        $this->is_hidden = $is_hidden;
        $this->type = AvpType::CALL_SERIAL_NUMBER_AVP;
    }

    public static function import($data) 
    {
        $avp = new self();
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$avp->is_mandatory = ($avp_flags_len & 32768) ? true : false;
		$avp->is_hidden = ($avp_flags_len & 16384) ? true : false;
		$avp->length = ($avp_flags_len & 1023);
		if (!$avp->is_hidden && $avp->length != 10) {
			throw new AVPException("Invalid length for Call Serial Number AVP");
		}
		list( , $avp->vendor_id) = unpack('n', $data[2].$data[3]);
		list( , $avp->type) = unpack('n', $data[4].$data[5]);
		list( , $avp->value) = unpack('L', $data[6].$data[7].$data[8].$data[9]);
		$avp->validate();
        return $avp;
	}

    public function setValue($value) 
    {
		 if ($value >= 0 && $value < 0xFFFFFFFF ) {
			$this->value = $value;
		 } else {
			 throw new AVPException("Invalid value for Call Serial Number");
		 }
		 return true;
	}

    protected function getEncodedValue() 
    {
        return pack('n', $this->value);
	}

    public function validate() 
    {
		if (!$this->is_mandatory) {
			throw new SessionException("Call Serial Number should be mandatory AVP");
		}
		if ($this->value < 0 || $this->value >= 0xFFFFFFFF) {
			throw new SessionException("Call Serial Number should be greater than 0 and less than 0xFFFF");
		}
	}
}
