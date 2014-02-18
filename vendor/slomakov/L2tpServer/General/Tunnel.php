<?php

/****
* This file is part of php-L2tpServer-server.
* Copyright (C) Sergei Lomakov <sergei@lomakov.net>
*
* php-L2tpServer-server is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* php-L2tpServer-server is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with php-L2tpServer-server.  If not, see <http://www.gnu.org/licenses/>.
*
*****/

namespace L2tpServer\General;

use L2tpServer\Constants\TunnelStates,
    L2tpServer\Constants\AvpType,
    L2tpServer\AVPs\AVPFactory;

class Tunnel {

	private $id;
	private $state;

	function __construct($tunnel_id) {
		// no problem with avps:
		$this->id = $tunnel_id;
		$this->state = TunnelStates::TUNNEL_STATE_SCCRQ;
	}

	function processRequest($avps) {
		switch($this->state) {
			case TunnelStates::TUNNEL_STATE_NULL:
				// How we can get here ? No tunnel, no packets , exception ?
			break;
			case TunnelStates::TUNNEL_STATE_SCCRQ: // We've got a request, let's answer then? :-)
				return $this->sendSCCRP();
			break;
			case TunnelStates::TUNNEL_STATE_SCCRP:
			break;
			case TunnelStates::TUNNEL_STATE_SCCCN:
			break;
			case TunnelStates::TUNNEL_STATE_STOPCCN:
			break;
			case TunnelStates::TUNNEL_STATE_HELLO:
			break;
			default:
				// ? Unknown state!
		}
		return ; // what do ween to return ? packet, Cap ;)
	}

	private function sendSCCRP() {
		$avps = array();
		// Construct all needed AVPs:
		$avp_mt = AVPFactory::createAVP(array('avp_type' => AvpType::MESSAGE_TYPE_AVP));
		$avp_mt->setValue(MT_SCCRP);
		$avps[] = $avp_mt;

		$avp_pv = AVPFactory::createAVP(array('avp_type' => AvpType::PROTOCOL_VERSION_AVP));
		$avp_pv->setValue(array('version' => 1, 'revision' => 0));
		$avps[] = $avp_pv;

		$avp_fp = AVPFactory::createAVP(array('avp_type' => AvpType::FRAMING_CAPABILITIES_AVP));
		$avp_fp->setValue(array( 'sync' => true, 'async' => false));
		$avps[] = $avp_fp;

		$avp_h = AVPFactory::createAVP(array('avp_type' => AvpType::HOSTNAME_AVP));
		$avp_h->setValue(php_uname("n"));
		$avps[] = $avp_h;

		$avp_tid = AVPFactory::createAVP(array('avp_type' => AvpType::ASSIGNED_TUNNEL_ID_AVP));
		$avp_tid->setValue($this->id);
		$avps[] = $avp_tid;

	}

}
