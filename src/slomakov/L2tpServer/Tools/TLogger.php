<?php
/**
 * Created by PhpStorm.
 * User: sergei
 * Date: 31.08.16
 * Time: 14:48
 */

namespace L2tpServer\Tools;

trait TLogger
{
    protected $logger;

    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        if (!$this->logger instanceof Logger) {
            throw new \Exception("Logger object is not set");
        }
        return $this->logger;
    }
}
