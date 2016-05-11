<?php
/**
 * Created by PhpStorm.
 * User: Sergei
 * Date: 16.02.14
 * Time: 15:05
 */

namespace L2tpServer\AVPs;


use L2tpServer\Constants\AvpType;
use L2tpServer\Exceptions\AVPException;

class VendorNameAVP extends BaseAVP
{
    const VENDOR_NAME = "PHP-L2TP";

    public function __construct($isHidden=0)
    {
        $this->type = AvpType::VENDOR_NAME_AVP;
        $this->value = self::VENDOR_NAME; // readonly value
        $this->is_hidden = $isHidden;
        $this->is_mandatory = 0;
        parent::__construct();
    }

    public function setValue($nothing)
    {
        $this->value = self::VENDOR_NAME;
        return true;
    }

    public static function import($data)
    {
        $avp = new self();
        list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
        $avp->is_mandatory = ($avp_flags_len & 32768) ? true : false;
        $avp->is_hidden = ($avp_flags_len & 16384) ? true : false;
        $avp->length = ($avp_flags_len & 1023);
        if ($avp->length < 6 ) {
            throw new AVPException("Invalid length for VendorNameAVP!");
        }
        list( , $avp->vendor_id) = unpack('n', $data[2].$data[3]);
        list( , $avp->type) = unpack('n', $data[4].$data[5]);
        $avp->value = substr($data, 6, $avp->length - 6);
        $avp->validate();
        return $avp;
    }

    protected function validate()
    {
        if ($this->is_mandatory) {
            throw new AVPException("VendorNameAVP must not be MANDATORY");
        }
    }

    protected function getEncodedValue()
    {
        return $this->value;
    }

} 