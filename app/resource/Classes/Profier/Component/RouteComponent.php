<?php
namespace Pentagonal\Profier\Component;

use Pentagonal\Profier\Abstracts\RouterAbstract;
use Pentagonal\Profier\App;
use Pentagonal\Profier\Collector;

/**
 * Class RouteComponent
 * @package Pentagonal\Profier\Component
 */
class RouteComponent extends RouterAbstract
{
    protected $name;
    protected $route;
    protected $collector;
    const DEFAULT_METHOD = 'ANY';

    /**
     * RouteComponent constructor.
     *
     * @param string     $name
     * @param string     $route
     * @param callable   $callable
     * @param mixed|null $method
     * @throws \ErrorException
     */
    public function __construct($name, $route, $callable, $method = null)
    {
        if (!App::exist('system.route.callable')) {
            App::register('system.route.callable', new Collector())->protectDependency();
        } elseif (!App::exist('system.route.callable') instanceof Collector) {
            throw new \ErrorException('Pre Reserved Application `system.route.callable` has been set with non standard object');
        }
        $this->collector = App::dependency('system.route.callable');
        $this->name = $this->sanitizeName($name);
        $this->setRoute($route);
        $this->setCallable($callable);
        $this->setMethod($method);
    }

    /**
     * Sanitize Route Name
     *
     * @param string $name
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function sanitizeName($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('Route Name Must Be as String');
        }
        return trim($name);
    }

    /**
     * Filter The methods
     *
     * @param string|array $methods
     * @return bool
     * @throws \InvalidArgumentException
     */
    protected function filterMethods($methods)
    {
        if (is_null($methods) || is_string($methods) && strtoupper(trim($methods)) === 'ANY' || $methods === true) {
            $this->enableMethods($this->getDisabledMethods());
            return true;
        }
        if (is_array($methods)) {
            // disable all methods
            $this->disableMethods($this->getEnabledMethods());
            foreach ($methods as $value) {
                $this->enableMethods($value);
            }
            return true;
        }
        if (!is_string($methods)) {
            throw new \InvalidArgumentException(
                'Method must be as array or string',
                E_USER_ERROR
            );
        }
        if (strpos($methods, '|') !== false) {
            return $this->filterMethods(explode('|', $methods));
        }
        // disable all methods
        $this->disableMethods($this->getEnabledMethods());
        $this->enableMethods($methods);
        return true;
    }

    /**
     * Set methods into route
     *
     * @param array $methods
     */
    public function setMethod($methods)
    {
        $this->filterMethods($methods);
    }

    /**
     * Set methods into route
     *
     * @param array $methods
     */
    public function setMethods($methods)
    {
        $this->filterMethods($methods);
    }

    /**
     * Set callable into route
     *
     * @param callable $callable
     * @throws \InvalidArgumentException
     */
    public function setCallable($callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Paramater must be callable');
        }
        $this->collector->set(
            $this->getName(),
            new CallableReconstructComponent($callable, $this->collector)
        );
    }

    /**
     * Set Route Regex
     *
     * @param string $string
     * @throws \InvalidArgumentException
     */
    public function setRoute($string)
    {
        if (!is_string($string)) {
            throw new \InvalidArgumentException(
                'Route Must be as string',
                E_USER_ERROR
            );
        }
        $this->route = $string;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getCallable()
    {
        return $this->collector->get($this->getName());
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }

    public function __invoke()
    {
        return call_user_func_array(array($this->collector[$this->getName()], '__invoke'), func_get_args());
    }
}
