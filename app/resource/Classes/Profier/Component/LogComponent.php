<?php
namespace Pentagonal\Profier\Component;

use Pentagonal\Profier\Abstracts\LogWriterAbstract;
use Pentagonal\Profier\Collector;
use Pentagonal\Profier\Exceptions\ProfierException;
use Pentagonal\Profier\Interfaces\LogInterface;
use Pentagonal\Profier\LogWriter;

/**
 * Class LogComponent
 * @package Pentagonal\Profier\Component
 */
class LogComponent implements LogInterface
{
    protected $writer;

    protected $logs;

    protected $logged;
    /**
     * Detailed debug information
     */
    const DEBUG = 1;
    /**
     * Interesting events
     *
     * Examples: User logs in, SQL logs.
     */
    const INFO = 2;
    /**
     * Uncommon events
     */
    const NOTICE = 3;
    /**
     * Exceptional occurrences that are not errors
     *
     * Examples: Use of deprecated APIs, poor use of an API,
     * undesirable things that are not necessarily wrong.
     */
    const WARNING = 4;
    /**
     * Runtime errors
     */
    const ERROR = 5;
    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     */
    const CRITICAL = 6;
    /**
     * Action must be taken immediately
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     */
    const ALERT = 7;
    /**
     * Urgent alert.
     */
    const EMERGENCY = 8;

    /**
     * @var array
     */
    protected static $levels = array(
        self::EMERGENCY => 'EMERGENCY',
        self::ALERT     => 'ALERT',
        self::CRITICAL  => 'CRITICAL',
        self::ERROR     => 'ERROR',
        self::WARNING   => 'WARNING',
        self::NOTICE    => 'NOTICE',
        self::INFO      => 'INFO',
        self::DEBUG     => 'DEBUG'
    );

    public function __construct(LogWriterAbstract $writer = null)
    {
        $this->writer = $writer === null ? new LogWriter() : $writer;
        $this->logged = new Collector(
            [
                'EMERGENCY' => new Collector(),
                'ALERT' => new Collector(),
                'CRITICAL' => new Collector(),
                'ERROR' => new Collector(),
                'WARNING' => new Collector(),
                'NOTICE' => new Collector(),
                'INFO' => new Collector(),
                'DEBUG' => new Collector(),
                'UNKNOWN' => new Collector(),
            ]
        );
        $this->logs = new Collector(
            [
                'EMERGENCY' => new Collector(),
                'ALERT' => new Collector(),
                'CRITICAL' => new Collector(),
                'ERROR' => new Collector(),
                'WARNING' => new Collector(),
                'NOTICE' => new Collector(),
                'INFO' => new Collector(),
                'DEBUG' => new Collector(),
                'UNKNOWN' => new Collector(),
            ]
        );
    }

    /**
     * Set Log Writer
     *
     * @param LogWriterAbstract|LogWriter $writer
     */
    public function setWriter(LogWriterAbstract $writer)
    {
        $this->writer = $writer;
    }

    protected function sanitizeMessageArray(array $message)
    {
        $default = [
            'line' => 0,
            'file' => '',
            'message' => null,
            'type' => null,
        ];

        $err = array_merge($default, $message);
        if (is_string($err['message'])) {
            $err['type'] = !isset(self::$levels[$err['type']]) ? $default['type'] : $err['type'];
            if (is_numeric($err['line'])) {
                $err['line'] = abs($err['line']);
                if (!is_int($err['line'])) {
                    $err['line'] = 0;
                }
            } else {
                $err['line'] = 0;
            }
            return $err;
        }

        return null;
    }

    protected function putIntoLog(array $message)
    {
        if ($message = $this->sanitizeMessageArray($message)) {
            $type = !isset(self::$levels[$message['type']]) ? 'UNKNOWN' : $message['type'];
            if ($this->logs->has($type)) {
                $exception  = new ProfierException($message['message'], null);
                $exception->setFile($message['file']);
                $exception->setLine($message['line']);
                $exception->setCode($message['type']);
                $this->logs[$type]->add($exception);
                return true;
            }
        }

        return false;
    }

    public function remove($type, $offset)
    {
        if ($this->logs->has($type)) {
            $this->logs[$type]->remove($offset);
        }
    }

    public function clear()
    {
        $this->clearLog();
        $this->clearLogged();
    }

    public function clearLog()
    {
        foreach ($this->logs as $value) {
            $value->clear();
        }
    }

    public function clearLogged()
    {
        foreach ($this->logged as $value) {
            $value->clear();
        }
    }

    public function clearLogType($type)
    {
        if ($this->logs->has($type)) {
            $this->logs[$type]->clear();
        }
    }
    public function clearLoggedType($type)
    {
        if ($this->logged->has($type)) {
            $this->logged[$type]->clear();
        }
    }
    public function save()
    {
        foreach ($this->logs as $key => $value) {
            foreach ($value as $k => $v) {
                $this->writer->write($v['exception']);
                $this->logged[$key]->set($k, $v);
                $value->remove($k);
            }
        }
    }

    public function arrayAdd(array $message)
    {
        return $this->putIntoLog($message);
    }

    protected function parsedLog($object)
    {
        if (is_array($object) || (is_object($object) && !method_exists($object, "__toString"))) {
            $message = print_r($object, true);
        } else {
            $message = (string) $object;
        }

        return $message;
    }

    public function add($level, $object, array $context = [])
    {
        $context['type'] = $level;
        $context['message'] = $this->parsedLog($object);
        return $this->arrayAdd($context);
    }

    public function info($object, $context = [])
    {
        return $this->add(self::INFO, $object, $context);
    }

    public function debug($object, $context = [])
    {
        return $this->add(self::DEBUG, $object, $context);
    }
    public function notice($object, $context = [])
    {
        return $this->add(self::NOTICE, $object, $context);
    }
    public function warning($object, $context = [])
    {
        return $this->add(self::WARNING, $object, $context);
    }
    public function error($object, $context = [])
    {
        return $this->add(self::ERROR, $object, $context);
    }
    public function critical($object, $context = [])
    {
        return $this->add(self::CRITICAL, $object, $context);
    }
    public function alert($object, $context = [])
    {
        return $this->add(self::ALERT, $object, $context);
    }
    public function emergency($object, $context = [])
    {
        return $this->add(self::EMERGENCY, $object, $context);
    }
    public function __destruct()
    {
        // doing auto save
        $this->save();
        $this->clear();
    }
}
