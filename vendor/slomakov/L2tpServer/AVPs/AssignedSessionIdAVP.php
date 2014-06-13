<?php

namespace L2tpServer\AVPs;

use L2tpServer\Exceptions\AVPException;
use L2tpServer\Constants\AvpType;

class AssignedSessionIdAVP extends BaseAVP
{
    public function __construct($is_hidden=false)
    {
        $this->is_mandatory = 1;
        $this->is_hidden = $is_hidden;
        $this->type = AvpType::ASSIGNED_SESSION_ID_AVP;
    }

    public static function import($data) 
    {
        $avp = new self();
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$avp->is_mandatory = ($avp_flags_len & 32768) ? true : false;
		$avp->is_hidden = ($avp_flags_len & 16384) ? true : false;
		$avp->length = ($avp_flags_len & 1023);
		if (!$avp->is_hidden && $avp->length != 8 ) {
			throw new AVPException("Invalid length for Assigned Session ID AVP");
		}
		list( , $avp->vendor_id) = unpack('n', $data[2].$data[3]);
		list( , $avp->type) = unpack('n', $data[4].$data[5]);
		list( , $avp->value) = unpack('n', $data[6].$data[7]);
		$avp->validate();
        return $avp;
	}

    public function setValue($value) 
    {
		 if ($value > 0 && $value < 0xFFFF ) {
			$this->value = $value;
		 } else {
			 throw new AVPException("Invalid value for Session ID");
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
			throw new SessionException("Assigned Session ID should be mandatory AVP");
		}
		if ($this->value == 0 || $this->value >= 0xFFFF) {
			throw new SessionException("Assigned Session ID should be greater than 0 and less than 0xFFFF");
		}
	}
}
