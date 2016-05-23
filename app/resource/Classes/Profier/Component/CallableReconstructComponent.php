<?php
namespace Pentagonal\Profier\Component;

use Closure;
use Pentagonal\Profier\Collector;
use RuntimeException;

/**
 * Class CallableReconstructComponent
 * @package Pentagonal\Profier\Component
 */
class CallableReconstructComponent
{
    protected $callable;
    protected $collector;

    public function __construct($callable, Collector $collector)
    {
        $this->callable = $callable;
        $this->collector = $collector;
    }

    /**
     * Resolve toResolve into a closure that that the router can dispatch.
     *
     * If toResolve is of the format 'class:method', then try to extract 'class'
     * from the container otherwise instantiate it and then dispatch 'method'.
     *
     * @param mixed $toResolve
     *
     * @return callable
     *
     * @throws \RuntimeException if the callable does not exist
     * @throws RuntimeException if the callable is not resolvable
     */
    public function resolve($toResolve)
    {
        $resolved = $toResolve;

        if (!is_callable($toResolve) && is_string($toResolve)) {
            // check for slim callable as "class:method"
            $callablePattern = '!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!';
            if (preg_match($callablePattern, $toResolve, $matches)) {
                $class = $matches[1];
                $method = $matches[2];

                if ($this->collector->has($class)) {
                    $resolved = [$this->collector->get($class), $method];
                } else {
                    if (!class_exists($class)) {
                        throw new RuntimeException(sprintf('Callable %s does not exist', $class));
                    }
                    $resolved = [new $class($this->collector), $method];
                }
            } else {
                // check if string is something in the DIC that's callable or is a class name which
                // has an __invoke() method
                $class = $toResolve;
                if ($this->collector->has($class)) {
                    $resolved = $this->collector->get($class);
                } else {
                    if (!class_exists($class)) {
                        throw new RuntimeException(sprintf('Callable %s does not exist', $class));
                    }
                    $resolved = new $class($this->collector);
                }
            }
        }

        if (!is_callable($resolved)) {
            throw new RuntimeException(sprintf('%s is not resolvable', $toResolve));
        }

        return $resolved;
    }

    public function __invoke()
    {
        $callable = $this->resolve($this->callable);
        if ($callable instanceof Closure) {
            $callable = $callable->bindTo($this->collector);
        }

        $args = func_get_args();

        return call_user_func_array($callable, $args);
    }
}
