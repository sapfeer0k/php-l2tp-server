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
 * Autoloader class
 * @author Sam. Special for http://www.freehabr.ru , 2011
 */
class Autoloader
{
    static protected $_paths = array();
    static protected $_classMap = array();
    /**
     * Регистрируем путь к библиотекам
     * @param string $path
     * @return void
     */
    static public function registerPath($path)
    {
        if(!in_array($path , self::$_paths))
             self::$_paths[] = $path;
    }
    /**
     * Загружаем класс  (spl_autoload_register вызывается если класс не загружен,
     * проверять на существование класс или интерфейс нет смысла)
     * @param string $class
     * @return boolean
     */
    public static function load($class)
    {
        if(!empty(self::$_classMap) && array_key_exists($class,self::$_classMap[$class])){
            require self::$_classMap[$class];
            return true;
        }

        $file = implode(DIRECTORY_SEPARATOR , array_map('ucfirst',explode('_', $class))) . '.php';

        foreach(self::$_paths as $path){
           if(file_exists($path . DIRECTORY_SEPARATOR . $file)){
                include $path . DIRECTORY_SEPARATOR . $file;
                return true;
           }
        }
        return false;
    }
   /**
    * Подключить карту расположения классов
    * @property string $path
    * @return array
    */
   static public function loadMap($path)
   {
        if(!file_exists($path)){
                self::$_classMap =  array();
                return;
        }
        self::$_classMap = include($path);
   }
}

?>
