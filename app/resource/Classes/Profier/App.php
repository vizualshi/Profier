<?php
namespace Pentagonal\Profier;

use Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory;
use Pentagonal\Profier\Component\ConfigComponent;
use Pentagonal\Profier\Component\Hooks;
use Pentagonal\Profier\Component\Input;

/**
 * Class App
 * @package Pentagonal\Profier
 */
class App extends DependencyObjectFactory
{
    /**
     * Initial Call method
     */
    public function init()
    {
        /**
         * register Application Collection & protect it
         */
        $this
            ->register('system.app', $this)
            ->protectDependency()
            ->register('system.input', new Input())
            ->protectDependency()
            ->register('system.hook', new Hooks())
            ->protectDependency()
            ->register('system.config', new ConfigComponent())
            ->protectDependency()
            ->register('system.route.callable', new Collector())
            ->protectDependency();
    }
}
