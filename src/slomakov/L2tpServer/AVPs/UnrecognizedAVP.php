<?php

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType;
use L2tpServer\Exceptions\AVPException;

class UnrecognizedAVP extends BaseAVP
{

    public function __construct()
    {
        parent::__construct();
    }

    public static function import($data)
    {
        $avp = new self();
        list(, $avpFlagsLength) = unpack('n', $data[0] . $data[1]);
        $avp->isMandatory = ($avpFlagsLength & 32768) ? 1 : 0;
        $avp->validate();
        return $avp;
    }

    protected function validate()
    {
        if ($this->isMandatory) {
            throw new AVPException("Unknown mandatory AVP!");
        } else {
            $this->is_ignored = 1;
        }
    }

    public function isIgnored()
    {
        return $this->is_ignored;
    }

    public function setValue($value)
    {
        // this avp isn't recognized and doesn't have values
        throw new AVPException("You can't change value for unrecognized AVP");
    }

    public function getType()
    {
        return AvpType::UNKNOWN_AVP;
    }

    protected function getEncodedValue()
    {
        throw new AVPException("You can't get encoded value for unrecognized AVP");
    }
}
