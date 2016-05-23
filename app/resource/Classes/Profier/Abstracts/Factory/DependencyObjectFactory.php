<?php
namespace Pentagonal\Profier\Abstracts\Factory;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use ArrayIterator;
use Pentagonal\Profier\Collector;

/**
 * Class DependencyObject - record object dependency and serve into as application
 *
 * @package \Pentagonal\Profier\Abstracts\Factory
 * @subpackage DependencyObject
 */
abstract class DependencyObjectFactory implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @var \Pentagonal\Profier\Collector
     */
    protected static $data;

    /**
     * @var \Pentagonal\Profier\Collector
     */
    protected static $protectedName;

    /**
     * @var null|string
     */
    protected static $currentKey;

    /**
     * @var array
     */
    protected static $removedApp = [];

    /**
     * @var \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory
     */
    private static $instance;

    /**
     * \Pentagonal\Profier\Factory\DependencyObject constructor.
     * @final
     */
    final public function __construct()
    {
        // prevent multiple set
        if (!is_object(self::$instance)) {
            self::$instance = $this;
            self::$data = new Collector();
            self::$protectedName = new Collector();
            $this->init();
        }
    }

    /**
     * @final
     * @return \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory
     */
    final public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Initialize
     *
     * @return mixed
     */
    abstract protected function init();

    /**
     * @param  string $key
     * @param  mixed  $default default returning if not exist
     * @return mixed
     */
    public static function getDependency($key, $default = null)
    {
        if (!is_string($key)) {
            return $default;
        }
        $key = trim($key);
        $Instance = self::getInstance();
        return $Instance::$data->retrieve($key, $default);
    }

    /**
     * @param string $key the name of object
     * @return bool
     */
    public static function exist($key)
    {
        if (!is_string($key)) {
            return false;
        }
        $key = trim($key);
        $Instance = self::getInstance();
        return $Instance::$data->has($key);
    }

    /**
     * @param string $key the name of object
     * @return bool
     */
    public static function isProtected($key)
    {
        if (!is_string($key)) {
            return false;
        }
        return self::exist($key) && self::$protectedName->has($key);
    }

    /**
     * @param string|null $key
     * @return DependencyObjectFactory
     */
    public static function protectDependency($key = null)
    {
        $Instance = self::getInstance();
        if ($key === null) {
            $key = $Instance::getCurrentName();
        }
        if (!self::isProtected($key) && self::exist($key)) {
            $Instance::$protectedName->set($key, true);
        }
        return $Instance;
    }

    /**
     * @param string $name      the name of object
     * @param object $callable  object of values
     *
     * @return DependencyObjectFactory extends
     */
    public static function register($name, $callable)
    {
        if (!is_object($callable)) {
            throw new \InvalidArgumentException('Parameter 2 must be object callable!');
        }
        if (!is_string($name)) {
            throw new \InvalidArgumentException('Parameter 1 of Application Name must be as string callable!');
        }
        // trim app
        $name = trim($name);
        $Instance = self::getInstance();
        if (self::isProtected($name)) {
            trigger_error('Could not override protected dependency', E_USER_NOTICE);
            return $Instance;
        }
        $Instance::$data->set($name, $callable);
        self::$currentKey = $name;
        return $Instance;
    }

    /**
     * @param string $key
     *
     * @return \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory static extends
     */
    public static function unregister($key)
    {
        $Instance = self::getInstance();
        if (self::isProtected($key)) {
            trigger_error('Could not unregister protected dependency', E_USER_NOTICE);
            return $Instance;
        }
        $key = trim($key);
        $Instance::$data->removeElement($key);
        if ($key == self::$currentKey) {
            $keys = $Instance::$data->getKeys();
            if (!empty($keys)) {
                self::$currentKey = end($keys);
            } else {
                self::$currentKey = null;
            }
        }

        self::$removedApp[] = $key;
        return $Instance;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function getCurrent($default = null)
    {
        if (self::$currentKey != null && self::exist(self::$currentKey)) {
            return self::getDependency(self::$currentKey);
        }

        return $default;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function getNext($default = null)
    {
        $current = self::getCurrentName();
        if ($current) {
            $Instance = self::getInstance();
            $keys = $Instance::getKeys();
            $keyCurrent = array_search($current, $keys, true);
            if (!is_int($keyCurrent)) {
                return $default;
            }
            if (isset($keys[$keyCurrent+1]) && ! self::exist($keys[$keyCurrent+1])) {
                return $default;
            }
            return self::get($keys[$keyCurrent], $default);
        }
        return $default;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function getPrev($default = null)
    {
        $current = self::getCurrentName();
        if ($current) {
            $Instance = self::getInstance();
            $keys = $Instance::getKeys();
            $keyCurrent = array_search($current, $keys, true);
            if (!is_int($keyCurrent)) {
                return $default;
            }
            if (isset($keys[$keyCurrent-1]) && ! self::exist($keys[$keyCurrent-1])) {
                return $default;
            }
            return self::get($keys[$keyCurrent-1], $default);
        }
        return $default;
    }

    /**
     * @return array|Collector
     */
    public static function getProtectedName()
    {
        return self::$protectedName->toArray();
    }

    /**
     * @return null|string
     */
    public static function getCurrentName()
    {
        return self::$currentKey;
    }

    /**
     * @return array
     */
    public static function getKeys()
    {
        return self::$data->getKeys();
    }

    /**
     * @return array
     */
    public static function getAll()
    {
        return self::$data->toArray();
    }

    /**
     * @param string $key
     * @param object $object
     *
     * @return \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory static extends
     */
    public static function set($key, $object)
    {
        return self::register($key, $object);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return self::getDependency($key, $default);
    }

    /**
     * @param string $key
     *
     * @return \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory static extends
     */
    public static function remove($key)
    {
        return self::unregister($key);
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function current($default= null)
    {
        return self::getCurrent($default);
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function next($default= null)
    {
        return self::getNext($default);
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function prev($default= null)
    {
        return self::getPrev($default);
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has($name)
    {
        return self::exist($name);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset An offset to check for.
     *
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset)
    {
        return self::has($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return self::getDependency($offset);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     *
     * @return \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory static extends
     */
    public function offsetSet($offset, $value)
    {
        return self::register($offset, $value);
    }

    /**
     * Required by interface ArrayAccess.
     *
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     *
     * @return \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory static extends
     */
    public function offsetUnset($offset)
    {
        return self::remove($offset);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        if (!self::isProtected($name) && is_object($value)) {
            return self::set($name, $value);
        }

        $this->$name = $value;
        return null;
    }

    /**
     * Required by interface IteratorAggregate.
     *
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator(self::$data);
    }

    /**
     * Required by interface Countable.
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     */
    public function count()
    {
        return count($this->getAll());
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (self::exist($name)) {
            return self::getDependency($name);
        }
        return property_exists($this, $name) ? $this->$name : null;
    }

    /**
     * Magic Method Calling Static Method
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        if (self::has($name)) {
            array_unshift($arguments, $name);
            return call_user_func_array(
                [
                    self::getInstance(),
                    'get'
                ],
                $arguments
            );
        }
        throw new \Exception(
            sprintf(
                'Call to undefined method %s',
                $name
            )
        );
    }

    /**
     * PHP5 Magic Method
     *
     * @return string
     */
    public function __toString()
    {
        return ''.spl_object_hash($this);
    }
}
