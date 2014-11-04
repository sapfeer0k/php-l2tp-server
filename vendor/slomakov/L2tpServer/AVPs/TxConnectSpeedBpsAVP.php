<?php

namespace L2tpServer\AVPs;

use L2tpServer\Exceptions\AVPException;
use L2tpServer\Constants\AvpType;
use L2tpServer\Exceptions\SessionException;

class TxConnectSpeedBpsAVP extends BaseAVP
{
    public function __construct($isHidden=false)
    {
        $this->is_mandatory = 1;
        $this->is_hidden = $isHidden;
        $this->type = AvpType::TX_CONNECT_SPEED_BPS_AVP;
    }

    public static function import($data) 
    {
        $avp = new self();
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$avp->is_mandatory = ($avp_flags_len & 32768) ? true : false;
		$avp->is_hidden = ($avp_flags_len & 16384) ? true : false;
		$avp->length = ($avp_flags_len & 1023);
		if (!$avp->is_hidden && $avp->length != 10) {
			throw new AVPException("Invalid length for (Tx) Connect Speed BPS AVP");
		}
		list( , $avp->vendor_id) = unpack('n', $data[2].$data[3]);
		list( , $avp->type) = unpack('n', $data[4].$data[5]);
		list( , $avp->value) = unpack('L', $data[6].$data[7].$data[8].$data[9]);
        // Unpack L doesn't worked, bug, see docs
        $avp->value = sprintf('%u', $avp->value);
		$avp->validate();
        return $avp;
	}

    public function setValue($value) 
    {
		 if ($value >= 0 && $value < 0xFFFFFFFF ) {
			$this->value = $value;
		 } else {
			 throw new AVPException("Invalid value for (Tx) Connect Speed BPS");
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
			throw new SessionException("(Tx) Connect Speed BPS should be mandatory AVP");
		}
		if ($this->value < 0 || $this->value >= 0xFFFFFFFF) {
            throw new SessionException("(Tx) Connect Speed BPS should be greater than 0 and less than 0x0xFFFFFFFF, got: {$this->value}");
		}
	}
}
