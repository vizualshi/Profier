<?php
namespace Pentagonal\Profier\Abstracts;

abstract Class RouterAbstract
{
    /**
     * List Available Method
     * @var array
     */
    protected $methods = [
        'CONNECT' => 1,
        'DELETE' => 1,
        'GET' => 1,
        'HEAD' => 1,
        'OPTIONS' => 1,
        'PATCH' => 1,
        'POST' => 1,
        'PUT' => 1,
        'TRACE' => 1,
    ];

    public function getEnabledMethods()
    {
        return array_keys($this->methods, 1, true);
    }

    public function getDisabledMethods()
    {
        return array_keys($this->methods, 0, true);
    }

    /**
     * Disable Route Method
     *
     * @param array|string $args
     * @return boolean
     */
    public function disableMethods($args)
    {
        if (is_array($args)) {
            foreach ($args as $arg) {
                if (is_string($arg)) {
                    $this->disableMethods($arg);
                }
            }
            return true;
        } elseif (is_string($args)) {
            $args = trim(strtoupper($args));
            if (isset($this->methods[$args])) {
                $this->methods[$args] = 0;
                return true;
            }
        }

        return false;
    }

    /**
     * Disable Route Method
     *
     * @param array|string $args
     * @return boolean
     */
    public function enableMethods($args)
    {
        if (is_array($args)) {
            foreach ($args as $arg) {
                if (is_string($arg)) {
                    $this->enableMethods($arg);
                }
            }
            return true;
        } elseif (is_string($args)) {
            $args = trim(strtoupper($args));
            if (isset($this->methods[$args])) {
                $this->methods[$args] = 1;
                return true;
            }
        }

        return false;
    }

}
