<?php
namespace Pentagonal\Profier\Interfaces;

interface RouterInterface extends \ArrayAccess, \Countable, \IteratorAggregate
{
    public function add(array $container);
    public function prepend(array $container, $before = null);
    public function append(array $container, $before = null);
    public function group(array $container);
    public function protect($name, $method = null);
    public function remove($name, $method = null);
}
