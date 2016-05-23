<?php
namespace Pentagonal\Profier;

use Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory;
use Pentagonal\Profier\Component\Hooks;
use Pentagonal\Profier\Component\Input;

/**
 * Class App
 * @package Pentagonal\Profier
 */
class App extends DependencyObjectFactory
{
    public function init()
    {
        // register collector & protect it
        $this
            ->register('system.app', $this)
            ->protectDependency()
            ->register('system.input', new Input())
            ->protectDependency()
            ->register('system.hook', new Hooks())
            ->protectDependency()
            ->register('system.config', new Config())
            ->protectDependency()
            ->register('system.route.callable', new Collector())
            ->protectDependency();
    }
}
