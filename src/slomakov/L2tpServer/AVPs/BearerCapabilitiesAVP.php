<?php

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType;
use L2tpServer\Exceptions\AVPException;

class BearerCapabilitiesAVP extends BaseAVP
{

    public function __construct($isHidden = 0)
    {
        $this->isMandatory = 1;
        $this->isHidden = $isHidden;
        parent::__construct();
    }

    public function setValue($nothing)
    {
        // this value is readonly!
        $this->value = array("analog" => 0, "digital" => 0);
        return true;
    }

    public static function import($data)
    {
        $avp = new self();
        list(, $avp_flags_len) = unpack('n', $data[0] . $data[1]);
        $avp->isMandatory = ($avp_flags_len & 32768) ? 1 : 0;
        $avp->isHidden = ($avp_flags_len & 16384) ? 1 : 0;
        $avp->length = ($avp_flags_len & 1023);
        if (!$avp->isHidden && $avp->length != 10) {
            throw new AVPException("Invalid length: {$avp->length} for Bearer Capabilities AVP!");
        }
        list(, $avp->vendor_id) = unpack('n', $data[2] . $data[3]);
        list(, $type) = unpack('n', $data[4] . $data[5]);
        $avp->value = array();
        list(, $flag_byte) = unpack('C', $data[9]);

        $avp->value["analog"] = ($flag_byte & 2) ? 1 : 0;
        $avp->value["digital"] = ($flag_byte & 1) ? 1 : 0;
        $avp->validate();
        return $avp;
    }

    public function getType()
    {
        return AvpType::BEARER_CAPABILITIES_AVP;
    }

    protected function validate()
    {
        if (!$this->isMandatory) {
            throw new \Exception("Avp {$this->getType()} must be MANDATORY");
        }
    }

    protected function getEncodedValue()
    {
        $value = $this->value['analog'] << 1 + $this->value['digital'];
        return pack('nn', 0, $value);
    }
}
