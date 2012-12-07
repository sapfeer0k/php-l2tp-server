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


class L2tp_Tunnel {

	private $id;
	private $state;

	function __construct($tunnel_id) {
		$this->state = Constants_TunnelStates::TUNNEL_STATE_NULL;
		// no problem with avps:
		$this->id = $tunnel_id;
		$this->state = Constants_TunnelStates::TUNNEL_STATE_SCCRQ;
	}

	function processRequest($avps) {
#		if ( $avps->find
		switch($this->state) {
			case Constants_TunnelStates::TUNNEL_STATE_NULL:
				// How we can get here ? No tunnel, no packets , exception ?
			break;
			case Constants_TunnelStates::TUNNEL_STATE_SCCRQ: // We've got a request, let's answer then? :-)
				$this->sendSCCRP();
			break;
			case Constants_TunnelStates::TUNNEL_STATE_SCCRP:
			break;
			case Constants_TunnelStates::TUNNEL_STATE_SCCCN:
			break;
			case Constants_TunnelStates::TUNNEL_STATE_STOPCCN:
			break;
			case Constants_TunnelStates::TUNNEL_STATE_HELLO:
			break;
			default:
				// ? Unknown state!
		}
		return ; // what do ween to return ? packet, Cap ;)
	}

	private function sendSCCRP() {
		$avps = array();
		// Construct all needed AVPs:
		$avp_mt = Factory::createAVP(array('avp_type' => Constants_AvpType::MESSAGE_TYPE_AVP));
		$avp_mt->setValue(MT_SCCRP);
		$avps[] = $avp_mt;

		$avp_pv = Factory::createAVP(array('avp_type' => Constants_AvpType::PROTOCOL_VERSION_AVP));
		$avp_pv->setValue(array('version' => 1, 'revision' => 0));
		$avps[] = $avp_pv;

		$avp_fp = Factory::createAVP(array('avp_type' => Constants_AvpType::FRAMING_CAPABILITIES_AVP));
		$avp_fp->setValue(array( 'sync' => true, 'async' => false));
		$avps[] = $avp_fp;

		$avp_h = Factory::createAVP(array('avp_type' => Constants_AvpType::HOSTNAME_AVP));
		$avp_h->setValue(php_uname("n"));
		$avps[] = $avp_h;

		$avp_tid = Factory::createAVP(array('avp_type' => Constants_AvpType::ASSIGNED_TUNNEL_ID_AVP));
		$avp_tid->setValue($this->id);
		$avps[] = $avp_tid;

		print_r($avps);
		die();
	}

}
