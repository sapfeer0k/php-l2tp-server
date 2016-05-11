<?php

namespace L2tpServer\AVPs;

use L2tpServer\Constants\AvpType;
use L2tpServer\Exceptions\AVPException;

class HostnameAVP extends BaseAVP {

    public function __construct()
    {
        $this->type = AvpType::HOSTNAME_AVP;
        $this->is_mandatory = 1;
        $this->is_hidden = 0;
        parent::__construct();
    }

	public static function import($data) {
        $avp = new self();
		list( , $avp_flags_len) = unpack('n', $data[0].$data[1]);
		$avp->is_mandatory = ($avp_flags_len & 32768) ? true : false;
		$avp->is_hidden = ($avp_flags_len & 16384) ? true : false;
		$avp->length = ($avp_flags_len & 1023);
		if ($avp->length < 1 ) {
			throw new AVPException("Invalid length for Hostname AVP!");
		}
		list( , $avp->vendor_id) = unpack('n', $data[2].$data[3]);
		list( , $avp->type) = unpack('n', $data[4].$data[5]);
		$avp->value = substr($data, 6, $avp->length - 6);
		$avp->validate();
        return $avp;
	}

	public function setValue($value) {
		// TODO: Possibly we have to check at least length ??
		$this->value = $value;
		return true;
	}

    protected function getEncodedValue()
    {
        return $this->value;
    }

	protected function validate() {
		if ($this->is_hidden) {
			throw new Exception("Hostname AVP must not be HIDDEN");
		}
	}

}
