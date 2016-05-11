<?php

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType,
    L2tpServer\Exceptions\AVPException,
    L2tpServer\Exceptions\TunnelException,
    L2tpServer\Exceptions\PackageException;

class MessageTypeAVP extends BaseAVP
{
    public function __construct()
    {
        $this->is_mandatory = 1;
        $this->is_hidden = 0;
        $this->type = AvpType::MESSAGE_TYPE_AVP;
        parent::__construct();
    }

    public static function import($data)
    {
        $avp = new self();
        list(, $avp_flags_len) = unpack('n', $data[0] . $data[1]);
        $avp->is_mandatory = ($avp_flags_len & 32768) ? 1 : 0;
        $avp->is_hidden = ($avp_flags_len & 16384) ? 1 : 0;
        $avp->length = ($avp_flags_len & 1023);
        list(, $avp->vendor_id) = unpack('n', $data[2] . $data[3]);
        list(, $avp->type) = unpack('n', $data[4] . $data[5]);
        list(, $avp->value) = unpack('n', $data[6] . $data[7]);
        $avp->validate();
        return $avp;
    }

    public function setValue($value)
    {
        if ($value >= 0 && $value < 65536) {
            $this->value = $value;
        } else {
            throw new AVPException("Invalid value for Message Type AVP");
        }
        return true;
    }

    protected function validate()
    {
        if ($this->length != 8) {
            if ($this->is_mandatory) {
                throw new TunnelException("Invalid length for Message Type AVP.");
            } else {
                throw new PackageException("Invalid Message Type AVP. Can be ignored.");
            }
        }
        if ($this->is_hidden) {
            if (!$this->is_mandatory) {
                throw new TunnelException("Invalid message type AVP. Message type AVP shouldn't be hidden.");
            } else {
                throw new PackageException("Invalid Message Type AVP. Can be ignored.");
            }
        }
    }

    protected function getEncodedValue()
    {
        return pack('n', $this->value);
    }
}
