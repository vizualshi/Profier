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
            ->register('system.input', function () {
                return new Input();
            })
            ->register('system.hook', function () {
                return new Hooks();
            })
            ->register('system.config', function () {
                return new ConfigComponent();
            })
            ->register('system.route.callable', function () {
                new Collector();
            })
            ->protectDependency([
                'system.input',
                'system.hook',
                'system.config',
                'system.route.callable'
            ]);
    }
}
