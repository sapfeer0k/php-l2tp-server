<?php

namespace L2tpServer\AVPs;

use L2tpServer\Exceptions\AVPException;
use L2tpServer\Constants\AvpType;
use L2tpServer\Exceptions\SessionException;

class ProxyAuthenTypeAVP extends BaseAVP
{
    const RESERVED = 0;
    const TEXTUAL_EXCHANGE = 1;
    const PPP_CHAP = 2;
    const PPP_PAP = 3;
    const PPP_NO_AUTHENTICATION = 4;
    const MSCHAP_V1 = 5;

    protected static $allowed = array (
        self::RESERVED, self::TEXTUAL_EXCHANGE, self::PPP_CHAP,
        self::PPP_PAP, self::PPP_NO_AUTHENTICATION, self::MSCHAP_V1,
    );

    public function __construct($isHidden = 0)
    {
        $this->isMandatory = 0;
        $this->isHidden = $isHidden;
        parent::__construct();
    }

    public static function import($data)
    {
        $avp = new self();
        list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
        $avp->isMandatory = ($avp_flags_len & 32768) ? 1 : 0;
        $avp->isHidden = ($avp_flags_len & 16384) ? 1 : 0;
        $avp->length = ($avp_flags_len & 1023);
        if (!$avp->isHidden && $avp->length != 8) {
            throw new AVPException("Invalid length for Assigned Session ID AVP");
        }
        list( , $avp->vendor_id) = unpack('n', $data[2].$data[3]);
        list( , $type) = unpack('n', $data[4].$data[5]);
        list( , $avp->value) = unpack('n', $data[6].$data[7]);
        $avp->validate();
        return $avp;
    }

    public function setValue($value)
    {
        if (!in_array($this->value, self::$allowed)) {
            throw new AVPException("Invalid value for Proxy Authen Type");
        }
         return true;
    }

    protected function getEncodedValue()
    {
        return pack('n', $this->value);
    }

    public function validate()
    {
        if ($this->isMandatory) {
            throw new SessionException("Proxy Authen Type should not be mandatory AVP");
        }
        if ($this->value == 0 || $this->value >= 0xFFFF) {
            throw new SessionException("Proxy Authen Type should be greater than 0 and less than 0xFFFF");
        }
    }

    public function getType()
    {
        return AvpType::PROXY_AUTHEN_TYPE_AVP;
    }
}
