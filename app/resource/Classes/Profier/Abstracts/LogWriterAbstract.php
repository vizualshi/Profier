<?php
namespace Pentagonal\Profier\Abstracts;

use Pentagonal\Profier\Component\LogComponent;

/**
 * Abstract Class LogWriterAbstract
 * @package Pentagonal\Profier\Abstracts
 */
abstract class LogWriterAbstract
{
    /**
     * @var array
     */
    protected $settings = array();
    /**
     * @var array
     */
    protected static $levels = array(
        LogComponent::EMERGENCY => 'EMERGENCY',
        LogComponent::ALERT     => 'ALERT',
        LogComponent::CRITICAL  => 'CRITICAL',
        LogComponent::ERROR     => 'ERROR',
        LogComponent::WARNING   => 'WARNING',
        LogComponent::NOTICE    => 'NOTICE',
        LogComponent::INFO      => 'INFO',
        LogComponent::DEBUG     => 'DEBUG'
    );

    /**
     * LogWriterAbstract constructor.
     * @param array $settings
     */
    public function __construct(array $settings = array())
    {
        $this->settings = $settings;
    }

    /**
     * @param  string|int $key
     * @param  mixed      $default
     *
     * @return mixed|null
     */
    public function getSetting($key, $default = null)
    {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }

    /**
     * Abstract of Write Log
     *
     * @abstract
     * @param \Exception $e
     * @return mixed
     */
    abstract public function write(\Exception $e);
}
