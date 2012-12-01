<?php

/****
* This file is part of php-l2tp-server.
* Copyright (C) Sergei Lomakov <sergei@lomakov.net>
*
* php-l2tp-server is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* php-l2tp-server is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with php-l2tp-server.  If not, see <http://www.gnu.org/licenses/>.
*
*****/

class constants_tunnel_state {
	const TUNNEL_STATE_NULL = 0;
	const TUNNEL_STATE_SCCRQ = 1;
	const TUNNEL_STATE_SCCRP = 2;
	const TUNNEL_STATE_SCCCN = 3;
	const TUNNEL_STATE_STOPCCN = 4;
	const TUNNEL_STATE_HELLO = 6;
}


class l2tp_tunnel {

	private $id;
	private $state;

	function __construct($avps) {
		$this->state = constants_tunnel_state::TUNNEL_STATE_NULL;
		// no problem with avps:
		if ( 0 ) print_r($avps);
		$this->state = constants_tunnel_state::TUNNEL_STATE_SCCRQ;
	}

	function processRequest() {
		switch($this->state) {
			case constants_tunnel_state::TUNNEL_STATE_NULL:
				// How we can get here ? No tunnel, no packets , exception ?
			break;
			case constants_tunnel_state::TUNNEL_STATE_SCCRQ: // We've got a request, let's answer then? :-)
				$this->sendSCCRP();
			break;
			case constants_tunnel_state::TUNNEL_STATE_SCCRP:
			break;
			case constants_tunnel_state::TUNNEL_STATE_SCCCN:
			break;
			case constants_tunnel_state::TUNNEL_STATE_STOPCCN:
			break;
			case constants_tunnel_state::TUNNEL_STATE_HELLO:
			break;
			default:
				// ? Unknown state!
		}
		return ; // what do ween to return ? packet, Cap ;)
	}

	private function sendSCCRP() {
		$avps = array();
		// Construct all needed AVPs:
		$avp_mt = factory::createAVP(constants_message_type::MESSAGE_TYPE_AVP, MT_SCCRP);
		$avps[] = $avp_mt;
/*
		$avp_pv = new l2tp_avp();
		$avp_pv->type = PROTOCOL_VERSION_AVP;
		$avp_pv->is_mandatory = 1;
		$avp_pv->is_hidden = 0;
		$avp_pv->value = '?';
		$avps[] = $avp_pv;

		$avp_fp = new l2tp_avp();
		$avp_fp->type = FRAMING_CAPABILITIES_AVP;
		$avp_fp->is_mandatory = 1;
		$avp_fp->is_hidden = 0;
		$avp_fp->value = '?';
		$avps[] = $avp_fp;

		$avp_h = new l2tp_avp();
		$avp_h->type = HOSTNAME_AVP;
		$avp_h->is_mandatory = 1;
		$avp_h->is_hidden = 0;
		$avp_h->value = '?';
		$avps[] = $avp_h;

		$avp_tid = new l2tp_avp();
		$avp_tid->type = ASSIGNED_TUNNEL_ID_AVP;
		$avp_tid->is_mandatory = 1;
		$avp_tid->is_hidden = 0;
		$avp_tid->value = $this->id;
		$avps[] = $avp_tid;

		print_r($avps);
		die();
 * 
 */
	}

}
