<?php

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType;
use L2tpServer\Constants\Protocol,
    L2tpServer\Exceptions\AVPException;

class ProtocolVersionAVP extends BaseAVP
{
    public function __construct()
    {
        $this->isHidden = 0;
        $this->isMandatory = 1;
        parent::__construct();
    }

    public static function import($data)
    {
        $avp = new self();
        list(, $avp_flags_len) = unpack('n', $data[0] . $data[1]);
        $avp->isMandatory = ($avp_flags_len & 32768) ? true : false;
        $avp->isHidden = ($avp_flags_len & 16384) ? true : false;
        $avp->length = ($avp_flags_len & 1023);
        if ($avp->length != 8) {
            throw new AVPException("Invalid length for protocol version AVP!");
        }
        list(, $avp->vendor_id) = unpack('n', $data[2] . $data[3]);
        list(, $type) = unpack('n', $data[4] . $data[5]);
        $avp->value = array();
        list(, $avp->value["version"]) = unpack('C', $data[6]);
        list(, $avp->value["revision"]) = unpack('C', $data[7]);
        $avp->validate();
        return $avp;
    }

    protected function validate()
    {
        if (!$this->isMandatory) {
            throw new AVPException("Protocol version AVP must be MANDATORY");
        }
        if ($this->isHidden) {
            throw new AVPException("Protocol version AVP must not be HIDDEN");
        }
        if ($this->value['version'] != Protocol::VERSION || $this->value['revision'] != Protocol::REVISION) {
            throw new AVPException("Protocol version doesn't supported");
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
        return AvpType::PROTOCOL_VERSION_AVP;
    }

}
