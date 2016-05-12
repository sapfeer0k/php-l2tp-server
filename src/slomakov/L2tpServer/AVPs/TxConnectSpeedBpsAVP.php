<?php

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType;
use L2tpServer\Exceptions\AVPException;
use L2tpServer\Exceptions\SessionException;

class TxConnectSpeedBpsAVP extends BaseAVP
{
    public function __construct($isHidden = false)
    {
        $this->isMandatory = 1;
        $this->isHidden = $isHidden;
        parent::__construct();
    }

    public static function import($data)
    {
        $avp = new self();
        list(, $avp_flags_len) = unpack('n', $data[0] . $data[1]);
        $avp->isMandatory = ($avp_flags_len & 32768) ? true : false;
        $avp->isHidden = ($avp_flags_len & 16384) ? true : false;
        $avp->length = ($avp_flags_len & 1023);
        if (!$avp->isHidden && $avp->length != 10) {
            throw new AVPException("Invalid length for (Tx) Connect Speed BPS AVP");
        }
        list(, $avp->vendor_id) = unpack('n', $data[2] . $data[3]);
        list(, $type) = unpack('n', $data[4] . $data[5]);
        list(, $avp->value) = unpack('L', $data[6] . $data[7] . $data[8] . $data[9]);
        // Unpack L doesn't worked, bug, see docs
        $avp->value = sprintf('%u', $avp->value);
        $avp->validate();
        return $avp;
    }

    public function validate()
    {
        if (!$this->isMandatory) {
            throw new SessionException("(Tx) Connect Speed BPS should be mandatory AVP");
        }
        if ($this->value < 0 || $this->value >= 0xFFFFFFFF) {
            throw new SessionException("(Tx) Connect Speed BPS should be greater than 0 and less than 0x0xFFFFFFFF, got: {$this->value}");
        }
    }

    public function setValue($value)
    {
        if ($value >= 0 && $value < 0xFFFFFFFF) {
            $this->value = $value;
        } else {
            throw new AVPException("Invalid value for (Tx) Connect Speed BPS");
        }
        return true;
    }

    public function getType()
    {
        return AvpType::TX_CONNECT_SPEED_BPS_AVP;
    }

    protected function getEncodedValue()
    {
        return pack('n', $this->value);
    }
}
