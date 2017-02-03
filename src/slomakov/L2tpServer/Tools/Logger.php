<?php
/**
 * Created by PhpStorm.
 * User: sergei
 * Date: 12.05.16
 * Time: 7:37
 */

namespace L2tpServer\Tools;

class Logger extends \Packfire\Logger\File
{
    protected static $instances;

    /**
     * @param $name
     * @return static
     */
    public static function factory($name)
    {
        $hash = md5($name);
        if (!isset(static::$instances[$hash])) {
            static::$instances[$hash] = new static($name);
        }
        return static::$instances[$hash];
    }
}
