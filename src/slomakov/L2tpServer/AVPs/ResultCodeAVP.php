<?php

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType;
use L2tpServer\Exceptions\AVPException;
use L2tpServer\Exceptions\TunnelException;
use L2tpServer\Exceptions\PackageException;

class ResultCodeAVP extends BaseAVP
{
    public function __construct()
    {
        $this->isMandatory = 1;
        $this->isHidden = 0;
        parent::__construct();
    }

    public static function import($data)
    {
        $avp = new self();
        list(, $avpFlagsLength) = unpack('n', $data[0] . $data[1]);
        $avp->isMandatory = ($avpFlagsLength & 32768) ? 1 : 0;
        $avp->isHidden = ($avpFlagsLength & 16384) ? 1 : 0;
        $avp->length = ($avpFlagsLength & 1023);
        list(, $avp->vendor_id) = unpack('n', $data[2] . $data[3]);
        list(, $type) = unpack('n', $data[4] . $data[5]);
        $avp->value = array();
        list(, $resultCode) = unpack('n', $data[6] . $data[7]);
        $avp->value['resultCode'] = $resultCode;
        if ($avp->length > 8) {
            list(, $errorCode) = unpack('n', $data[8] . $data[9]);
            $avp->value['errorCode'] = $errorCode;
        }
        if ($avp->length > 10) {
            $avp->value['errorMessage'] = substr($data, 10, $avp->length - 10);
        }
        $avp->validate();
        return $avp;
    }

    public function setValue($value)
    {
        throw new \Exception("Implement me!");
    }

    protected function validate()
    {
        if ($this->length < 8) {
            if ($this->isMandatory) {
                throw new TunnelException("Invalid length for Result Code AVP.");
            } else {
                throw new PackageException("Invalid Result Code AVP. Can be ignored.");
            }
        }
        if ($this->isHidden) {
            if (!$this->isMandatory) {
                throw new TunnelException("Invalid Result Code AVP. Result Code AVP shouldn't be hidden.");
            } else {
                throw new PackageException("Invalid Result Code AVP. Can be ignored.");
            }
        }
    }

    protected function getEncodedValue()
    {
        throw new \Exception("Imlement me");
        return pack('n', $this->value);
    }

    public function getType()
    {
        return AvpType::RESULT_CODE_AVP;
    }
}
