<?php
namespace Pentagonal\Profier\Component;

use Pentagonal\Profier\Collector;
use Pentagonal\Profier\Traits\ArrayBracketResolver;

/***
 * Class ArrayStringParser
 * @package Pentagonal\Profier\Component
 */
class ArrayStringParser
{
    /**
     * Object use trait
     * ArrayBracketResolver
     */
    use ArrayBracketResolver;

    /**
     * ArrayStringParser constructor.
     *
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $this->setData($args);
    }

    /**
     * Get collector
     *
     * @return array
     */
    public function data()
    {
        return $this->getData();
    }

    /**
     * Get collector object
     *
     * @return Collector
     */
    public function getDataCollector()
    {
        /**
         * Create new Collector Object
         */
        return new Collector($this->getData());
    }
}
