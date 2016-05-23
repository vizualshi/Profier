<?php
namespace Pentagonal\Profier;

use Pentagonal\Profier\Abstracts\LogWriterAbstract;

/**
 * Class LogWriter
 * @package Pentagonal\Profier
 */
class LogWriter extends LogWriterAbstract
{
    public function write(\Exception $e)
    {
        return true;
    }

    public function getLogLevel($level, $default = null)
    {
        return isset(self::$levels[$level]) ? self::$levels[$level]: $default;
    }
}
