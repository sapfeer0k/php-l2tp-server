<?php

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType,
    L2tpServer\Exceptions\AVPException,
    L2tpServer\Exceptions\TunnelException,
    L2tpServer\Exceptions\PackageException;

class MessageTypeAVP extends BaseAVP
{

    public function setValue($value)
    {
        if ($value >= 0 && $value < 65536) {
            $this->value = $value;
        } else {
            throw new AVPException("Invalid value for Message Type AVP");
        }
        return true;
    }

    public function encode()
    {
        //throw new Exceptions("Encode method isn't defined");
        $flags = 0;
        if ($this->is_mandatory) {
            $flags += 32768;
        }
        $this->length = 6 + 2; // flags, len, type + value
        return pack("CCnnn", $flags, $this->length, 0x01, AvpType::MESSAGE_TYPE_AVP, $this->value);
        // this AVPs mustn't be hidden

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
        if (!AvpType::exists($this->value)) {
            if ($this->is_mandatory) {
                throw new TunnelException("Invalid message type AVP. Tunnel must be terminated.");
            } else {
                throw new PackageException("Invalid Message Type AVP. Can be ignored.");
            }
        }
    }

    protected function parse($data)
    {
        list(, $avp_flags_len) = unpack('n', $data[0] . $data[1]);
        $this->is_mandatory = ($avp_flags_len & 32768) ? true : false;
        $this->is_hidden = ($avp_flags_len & 16384) ? true : false;
        $this->length = ($avp_flags_len & 1023);
        list(, $this->vendor_id) = unpack('n', $data[2] . $data[3]);
        list(, $this->type) = unpack('n', $data[4] . $data[5]);
        list(, $this->value) = unpack('n', $data[6] . $data[7]);
        $this->validate();
    }
}
