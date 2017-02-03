<?php

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType;
use L2tpServer\Exceptions\AVPException;

class FirmwareRevisionAVP extends BaseAVP
{
    public function __construct($isHidden = 0)
    {
        $this->isMandatory = 0;
        $this->isHidden = $isHidden;
        parent::__construct();
    }

    public static function import($data)
    {
        $avp = new static();
        list(, $avp_flags_len) = unpack('n', $data[0] . $data[1]);
        $avp->isMandatory = ($avp_flags_len & 32768) ? 1 : 0;
        $avp->isHidden = ($avp_flags_len & 16384) ? 1 : 0;
        $avp->length = ($avp_flags_len & 1023);
        if ($avp->length != 8) {
            throw new AVPException("Invalid length for Firmware Revision");
        }
        list(, $avp->vendor_id) = unpack('n', $data[2] . $data[3]);
        list(, $type) = unpack('n', $data[4] . $data[5]);
        list(, $avp->value) = unpack('n', $data[6] . $data[7]);
        $avp->validate();
        return $avp;
    }

    public function setValue($value)
    {
        if ($value >= 0 && $value < 65535) {
            $this->value = $value;
        } else {
            throw new AVPException("Invalid value for Firmware Revision");
        }
        return true;
    }

    public function getType()
    {
        return AvpType::FIRMWARE_REVISION_AVP;
    }

    protected function getEncodedValue()
    {
        return pack('n', $this->value);
    }

    protected function validate()
    {
        if ($this->isMandatory) {
            //throw new AVPException("Firmware Revision should not be mandatory!");
        }
    }
}
