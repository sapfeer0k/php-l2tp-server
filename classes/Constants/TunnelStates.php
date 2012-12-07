<?php

/*
 * This file is a part of php-l2tp-server.
 * Copyright (C) "Sergei Lomakov <sergei@lomakov.net>"
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
 */

/**
 * Description of constants_tunnel
 *
 * @author "Sergei Lomakov <sergei@lomakov.net>"
 */
class Constants_TunnelStates {
	const TUNNEL_STATE_NULL = 0;
	const TUNNEL_STATE_SCCRQ = 1;
	const TUNNEL_STATE_SCCRP = 2;
	const TUNNEL_STATE_SCCCN = 3;
	const TUNNEL_STATE_STOPCCN = 4;
	const TUNNEL_STATE_HELLO = 6;
}


?>
