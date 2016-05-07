<?php

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType;
use L2tpServer\Constants\Protocol,
    L2tpServer\Exceptions\AVPException;

class RxConnectSpeedAVP extends BaseAVP
{
    public function __construct()
    {
        $this->is_hidden = 0;
        $this->is_mandatory = 0;
        $this->type = AvpType::RX_CONNECT_SPEED_AVP;
    }

    public static function import($data)
    {
        $avp = new self();
        list(, $avp_flags_len) = unpack('n', $data[0] . $data[1]);
        $avp->is_mandatory = ($avp_flags_len & 32768) ? true : false;
        $avp->is_hidden = ($avp_flags_len & 16384) ? true : false;
        $avp->length = ($avp_flags_len & 1023);
        if ($avp->length != 10) {
            throw new AVPException("Invalid length for Rx Connect Speed AVP!");
        }
        list(, $avp->vendor_id) = unpack('n', $data[2] . $data[3]);
        list(, $avp->type) = unpack('n', $data[4] . $data[5]);
        $avp->value = array();
        list(, $avp->value["low"]) = unpack('n', $data[6] . $data[7]);
        list(, $avp->value["high"]) = unpack('n', $data[8] . $data[9]);
        $avp->validate();
        return $avp;
    }

    protected function validate()
    {
        if ($this->is_mandatory) {
            throw new AVPException("Protocol version AVP must not be MANDATORY");
        }
        if ($this->is_hidden) {
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

}
