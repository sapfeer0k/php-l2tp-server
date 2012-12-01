<?php

define('TUNNEL_STATE_NULL', 0);
define('TUNNEL_STATE_SCCRQ', 1);
define('TUNNEL_STATE_SCCRP', 2);
define('TUNNEL_STATE_SCCCN', 3);
define('TUNNEL_STATE_STOPCCN', 4);
define('TUNNEL_STATE_HELLO', 6);


class l2tp_tunnel {

	private $id;
	private $state;
	
	function __construct($avps) {
		$this->state = TUNNEL_STATE_NULL;
		// no problem with avps:
#		print_r($avps);
		$this->state = TUNNEL_STATE_SCCRQ;
	}

	function processRequest() {
		switch($this->state) {
			case TUNNEL_STATE_NULL:
				// How we can get here ? No tunnel, no packets
			break;
			case TUNNEL_STATE_SCCRQ: // We've got a request, let's answer then? :-)
				$this->sendSCCRP();
			break;
			case TUNNEL_STATE_SCCRP:
			break;
			case TUNNEL_STATE_SCCCN:
			break;
			case TUNNEL_STATE_STOPCCN:
			break;
			case TUNNEL_STATE_HELLO:
			break;
			default:
				// ? Unknown state!
		}
		return ; // what do ween to return ?
	}

	private function sendSCCRP() {
		$avps = array();
		// Construct all needed AVPs:
		$avp = new l2tp_avp();
		$avp->type = MESSAGE_TYPE_AVP;
		$avp->is_mandatory = 1;
		$avp->is_hidden = 0;
		$avp->value = MT_SCCRP;
		$avps[] = $avp;
	
		$avp = new l2tp_avp();
		$avp->type = PROTOCOL_VERSION_AVP;
		$avp->is_mandatory = 1;
		$avp->is_hidden = 0;
		$avp->value = '?';
		$avps[] = $avp;

		$avp = new l2tp_avp();
		$avp->type = FRAMING_CAPABILITIES_AVP;
		$avp->is_mandatory = 1;
		$avp->is_hidden = 0;
		$avp->value = '?';
		$avps[] = $avp;

		$avp = new l2tp_avp();
		$avp->type = HOSTNAME_AVP;
		$avp->is_mandatory = 1;
		$avp->is_hidden = 0;
		$avp->value = '?';
		$avps[] = $avp;

		$avp = new l2tp_avp();
		$avp->type = ASSIGNED_TUNNEL_ID_AVP;
		$avp->is_mandatory = 1;
		$avp->is_hidden = 0;
		$avp->value = $this->id;
		$avps[] = $avp;

		print_r($avps);
		die();
	}
	
}
