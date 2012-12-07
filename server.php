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

define('L2TP_PHP_SERVER_VERSION', 0x01);

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('Autoloader.php');

Autoloader::registerPath(dirname(__FILE__).'/classes/');

spl_autoload_register(array('Autoloader' , 'load'));

Autoloader::registerPath(dirname(__FILE__).'/classes/');

$l2tp_server = new L2tp_Server();
$l2tp_server->run();
