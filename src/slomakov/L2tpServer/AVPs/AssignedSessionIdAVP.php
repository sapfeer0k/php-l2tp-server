<?php

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType;
use L2tpServer\Exceptions\AVPException;
use L2tpServer\Exceptions\SessionException;

class AssignedSessionIdAVP extends BaseAVP
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
        if (!$avp->isHidden && $avp->length != 8) {
            throw new AVPException("Invalid length for Assigned Session ID AVP");
        }
        list(, $avp->vendor_id) = unpack('n', $data[2] . $data[3]);
        list(, $type) = unpack('n', $data[4] . $data[5]);
        list(, $avp->value) = unpack('n', $data[6] . $data[7]);
        $avp->validate();
        return $avp;
    }

    public function validate()
    {
        if (!$this->isMandatory) {
            throw new SessionException("Assigned Session ID should be mandatory AVP");
        }
        if ($this->value == 0 || $this->value >= 0xFFFF) {
            throw new SessionException("Assigned Session ID should be greater than 0 and less than 0xFFFF");
        }
    }

    public function setValue($value)
    {
        if ($value > 0 && $value < 0xFFFF) {
            $this->value = $value;
        } else {
            throw new AVPException("Invalid value for Session ID");
        }
        return true;
    }

    public function getType()
    {
        return AvpType::ASSIGNED_SESSION_ID_AVP;
    }

    protected function getEncodedValue()
    {
        return pack('n', $this->value);
    }
}
