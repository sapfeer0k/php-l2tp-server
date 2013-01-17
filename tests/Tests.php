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
 * Description of Tests
 *
 * @author "Sergei Lomakov <sergei@lomakov.net>"
 */

require_once 'PHPUnit/Autoload.php';
require_once 'Autoloader.php';

spl_autoload_register(array('Autoloader' , 'load'));

Autoloader::registerPath(dirname(__FILE__).'/../classes/');

require_once('L2tp_AVP_MessageTypeTest.php');
require_once('L2tp_AVP_AssignedTunnelIdTest.php');


// подключаем файл с набором тестов


class Tests {
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('AllMySuite');
        // добавляем набор тестов
		$suite->addTestSuite('L2tp_AVP_MessageTypeTest');
		$suite->addTestSuite('L2tp_AVP_AssignedTunnelIdTest');
        return $suite;
    }
}

?>
