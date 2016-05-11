<?php

namespace L2tpServer\AVPs;

use L2tpServer\Exceptions\AVPException;

class UnrecognizedAVP extends BaseAVP
{

    public function __construct()
    {
        $this->type = -1;
    }

    public static function import($data)
    {
        $avp = new self();
        list(, $avp_flags_len) = unpack('n', $data[0] . $data[1]);
        $avp->is_mandatory = ($avp_flags_len & 32768) ? true : false;
        $avp->validate();
        return $avp;
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

    protected function validate()
    {
        if ($this->is_mandatory) {
            throw new AVPException("Unknown mandatory AVP!");
        } else {
            $this->is_ignored = true;
        }
    }

    protected function getEncodedValue()
    {
        throw new AVPException("You can't get encoded value for unrecognized AVP");
    }

}
