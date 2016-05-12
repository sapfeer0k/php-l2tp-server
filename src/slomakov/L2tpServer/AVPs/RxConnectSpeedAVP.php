<?php

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType;
use L2tpServer\Constants\Protocol;
use L2tpServer\Exceptions\AVPException;

class RxConnectSpeedAVP extends BaseAVP
{
    public function __construct()
    {
        $this->isHidden = 0;
        $this->isMandatory = 0;
        parent::__construct();
    }

    public static function import($data)
    {
        $avp = new self();
        list(, $avp_flags_len) = unpack('n', $data[0] . $data[1]);
        $avp->isMandatory = ($avp_flags_len & 32768) ? true : false;
        $avp->isHidden = ($avp_flags_len & 16384) ? true : false;
        $avp->length = ($avp_flags_len & 1023);
        if ($avp->length != 10) {
            throw new AVPException("Invalid length for Rx Connect Speed AVP!");
        }
        list(, $avp->vendor_id) = unpack('n', $data[2] . $data[3]);
        list(, $type) = unpack('n', $data[4] . $data[5]);
        $avp->value = array();
        list(, $avp->value["low"]) = unpack('n', $data[6] . $data[7]);
        list(, $avp->value["high"]) = unpack('n', $data[8] . $data[9]);
        $avp->validate();
        return $avp;
    }

    protected function validate()
    {
        if ($this->isMandatory) {
            throw new AVPException("Protocol version AVP must not be MANDATORY");
        }
        if ($this->isHidden) {
            throw new AVPException("Protocol version AVP must not be HIDDEN");
        }
    }

    public function setValue($value)
    {
        if (is_array($value) && isset($value['version']) && isset($value['revision'])) {
            $this->value = $value;
        } else {
            throw new AVPException("Invalid value for protocol type AVP");
        }
        return true;
    }

    public function getEncodedValue()
    {
        $value = ($this->value['version'] << 8) + $this->value['revision'];
        return pack('n', $value);
    }

    public function getType()
    {
        return AvpType::RX_CONNECT_SPEED_AVP;
    }
}
