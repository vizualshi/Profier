<?php
/**
 * Dependency Collector data injection
 * @package Pentagonal\Profier\Abstracts\Factory
 */
namespace Pentagonal\Profier\Abstracts\Factory;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use ArrayIterator;
use Pentagonal\Profier\Collector;
use Pentagonal\Profier\Component\CallableReconstructComponent;
use Pentagonal\Profier\Component\LogComponent;

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
    private static $data;

    /**
     * @var \Pentagonal\Profier\Collector
     */
    private static $protectedName;

    /**
     * @var null|string
     */
    private static $currentKey;

    /**
     * @var array
     */
    private static $removedApp = [];

    /**
     * @var \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory
     */
    private static $instance;

    /**
     * Prefix allowed Protected Name
     * @var \Pentagonal\Profier\Collector
     */
    private static $prefixProtectedName;

    /**
     * \Pentagonal\Profier\Factory\DependencyObject constructor.
     * @final
     */
    final public function __construct()
    {
        $this->initialConstruct();
    }

    final protected function initialConstruct()
    {
        // prevent multiple set
        if (!is_object(self::$instance)) {
            self::$instance = $this;
            self::$data = new Collector();
            self::$protectedName = new Collector();
            self::$prefixProtectedName = new Collector();
            // set default
            $this->setPrefixAllowedProtected('system');
            // add logs
            $this
                ->set(
                    'system.log',
                    function () {
                        return new LogComponent();
                    }
                )
                ->set('system.app', $this)
                ->protectDependency(['system.log', 'system.app'])
                ->init();
        }

        return $this;
    }

    /**
     * @param string $key
     * @return null|string
     */
    final public static function trimName($key)
    {
        if (is_string($key)) {
            return trim($key);
        }
        return null;
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
     * @param string          $name     the name of object
     * @param object|callable $callable object of values
     *
     * @return \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory instance of
     */
    public static function register($name, $callable)
    {
        $Instance = self::getInstance();
        return $Instance->set($name, $callable);
    }

    /**
     * @param string          $name     the name of object
     * @param object|callable $callable object of values
     *
     * @return \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory instance of
     */
    final public function set($name, $callable)
    {
        if (!is_object($callable) && !is_callable($callable)) {
            throw new \InvalidArgumentException('Parameter 2 must be callable!');
        }

        // trim app
        $name = $this->trimName($name);
        if (!$name) {
            throw new \InvalidArgumentException('Parameter 1 of application name must be as string and Could not be empty!');
        }
        if ($this->isProtected($name)) {
            trigger_error(
                sprintf('Could not override protected dependency of \'%s\'', $name),
                E_USER_NOTICE
            );
            return $this;
        }
        $this->getObjectData()->set(
            $name,
            [
                'raw'  => false,
                'data' => (!is_object($callable) ? new CallableReconstructComponent($callable, $this) : $callable)
            ]
        );

        self::$currentKey = $name;
        return $this;
    }

    /**
     * @param  string $key
     * @param  mixed  $default default returning if not exist
     * @return mixed
     */
    final public function getDependency($key, $default = null)
    {
        $key = $this->trimName($key);
        if (empty($key)) {
            return $default;
        }
        if ($this->has($key)) {
            $retrieve = $this->getObjectData()->retrieve($key, $default);
            if ($retrieve === $default) {
                return $default;
            }
            if (!empty($retrieve['raw'])
                || ! $retrieve['data'] instanceof \Closure && (
                    is_object($retrieve['data'])
                    || !method_exists($retrieve['data'], '__invoke')
                )
            ) {
                $retrieve['raw'] = true;
                return $retrieve['data'];
            }

            $retrieve['raw'] = true;
            $retrieve['data'] = $retrieve['data']($this);
            return $retrieve['data'];
        }

        return $default;
    }

    /**
     * @param  string $key
     * @param  mixed  $default default returning if not exist
     * @return mixed
     */
    public static function dependency($key, $default = null)
    {
        $Instance = self::getInstance();
        return $Instance->getObjectData()->retrieve($key, $default);
    }


    /**
     * @return \Pentagonal\Profier\Collector
     */
    final protected function getObjectData()
    {
        return self::$data;
    }

    /**
     * @return \Pentagonal\Profier\Collector
     */
    final protected static function objectData()
    {
        $Instance = self::getInstance();
        return $Instance->getObjectData();
    }

    /**
     * @return \Pentagonal\Profier\Collector
     */
    final protected function getObjectProtected()
    {
        return self::$protectedName;
    }

    /**
     * @return \Pentagonal\Profier\Collector
     */
    final protected static function objectProtected()
    {
        $Instance = self::getInstance();
        return $Instance->getObjectProtected();
    }

    /**
     * @return \Pentagonal\Profier\Collector
     */
    final protected function getObjectPrefixProtected()
    {
        return self::$prefixProtectedName;
    }

    /**
     * @return \Pentagonal\Profier\Collector
     */
    protected static function objectPrefixProtected()
    {
        $Instance = self::getInstance();
        return $Instance->getObjectPrefixProtected();
    }

    /**
     * @param string $key
     *
     * @return \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory instance
     */
    final public static function setPrefixAllowedProtected($key)
    {
        $instance = self::getInstance();
        $key = self::trimName($key);
        if (!$key) {
            return $instance;
        }

        $instance->getObjectPrefixProtected()->set($key, true);
        return $instance;
    }

    /**
     * @param string $key
     * @return bool|null
     */
    protected function isAllowedProtect($key)
    {
        if (!$this->has($key)) {
            return null;
        }
        $protected = $this->getObjectPrefixProtected();
        if ($protected->count() === 0) {
            return true;
        }
        $key = explode('.', $key);
        array_pop($key);
        if (count($key) < 1) {
            return false;
        }
        /**
         * Doing Loop
         * When contains as alowed protect
         */
        while (count($key)) {
            if ($protected->has(implode('.', $key))) {
                return true;
            }
            array_pop($key);
        }

        return false;
    }

    /**
     * @param string $name the name of object
     * @return bool
     */
    final public function has($name)
    {
        $name = $this->trimName($name);
        if (empty($name)) {
            return false;
        }
        return $this->getObjectData()->has($name);
    }

    /**
     * @param string $key the name of object
     * @return bool
     */
    public static function exist($key)
    {
        $Instance = self::getInstance();
        return $Instance->has($key);
    }

    /**
     * @param string $key the name of object
     * @return bool|null
     */
    final public function hasProtected($key)
    {
        if (!$this->has($key)) {
            return null;
        }
        return $this->getObjectProtected()->has(trim($key));
    }

    /**
     * @param string $key the name of object
     * @return bool|null
     */
    public static function isProtected($key)
    {
        $Instance = self::getInstance();
        return $Instance->hasProtected($key);
    }

    /**
     * @param string|null|array $key
     * @return \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory instance of
     */
    final public function protectDependency($key = null)
    {
        if (is_array($key)) {
            foreach ($key as $k) {
                if (is_string($k)) {
                    $this->protectDependency($k);
                }
            }

            return $this;
        }

        if ($key === null) {
            $key = $this->getCurrentName();
        }
        if (!$this->isAllowedProtect($key)) {
//            $this
//                ->get('system.log')
//                ->debug(
//                    sprintf('Application Name : \'%s\' does not allow to be protect', $key)
//                );
            return $this;
        }
        if ($this->isProtected($key) !== false) {
//            $this
//                ->get('system.log')
//                ->debug(
//                    sprintf('Application Name : \'%s\' does not exists or has been protected before!', $key)
//                );
            return $this;
        }

        $this->getObjectProtected()->set($key, true);
        return $this;
    }

    /**
     * @param string|null $key
     * @return \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory instance of
     */
    public static function protect($key = null)
    {
        $Instance = self::getInstance();
        return $Instance->protectDependency($key);
    }

    /**
     * @param string $key
     *
     * @return \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory instance of
     */
    public static function unregister($key)
    {
        $Instance = self::getInstance();
        return $Instance->remove($key);
    }

    /**
     * @param string $key
     *
     * @return \Pentagonal\Profier\Abstracts\Factory\DependencyObjectFactory instance of
     */
    final public function remove($key)
    {
        $key = $this->trimName($key);
        if (!$key) {
            trigger_error(
                'Invalid Key name dependency,  application name must be as string and Could not be empty',
                E_USER_NOTICE
            );
            return $this;
        }
        if ($this->isProtected($key) === true) {
            trigger_error('Could not unregister protected dependency', E_USER_NOTICE);
            return $this;
        }
        if ($this->getObjectData()->remove($key) !== null) {
            if ($key == $this->getCurrentName()) {
                $keys = $this->getObjectData()->last();
                self::$currentKey = $keys ? $keys : null;
            }
            self::$removedApp[$key] = time();
        }

        return $this;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public function getCurrent($default = null)
    {
        $current = $this->getCurrentName();
        if ($current != null && $this->has($current)) {
            return $this->getDependency($current);
        }

        return $default;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function current($default= null)
    {
        $Instance = self::getInstance();
        return $Instance->getCurrent($default);
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    final public function getNext($default = null)
    {
        $current = $this->getCurrentName();
        if ($current) {
            $keys = $this->getKeys();
            $keyCurrent = array_search($current, $keys, true);
            if (!is_int($keyCurrent)) {
                return $default;
            }
            if (isset($keys[$keyCurrent+1]) && ! $this->has($keys[$keyCurrent+1])) {
                return $default;
            }
            return $this->getDependency($keys[$keyCurrent], $default);
        }
        return $default;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function next($default= null)
    {
        $Instance = self::getInstance();
        return $$Instance->getNext($default);
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    final public function getPrev($default = null)
    {
        $current = $this->getCurrentName();
        if ($current) {
            $keys = $this->getKeys();
            $keyCurrent = array_search($current, $keys, true);
            if (!is_int($keyCurrent)) {
                return $default;
            }
            if (isset($keys[$keyCurrent-1]) && ! $this->has($keys[$keyCurrent-1])) {
                return $default;
            }
            return $this->getDependency($keys[$keyCurrent-1], $default);
        }

        return $default;
    }

    /**
     * @param mixed $default
     * @return mixed
     */
    public static function prev($default= null)
    {
        $Instance = self::getInstance();
        return $Instance->getPrev($default);
    }

    /**
     * @return array|Collector
     */
    final public function getProtectedName()
    {
        return self::$protectedName->toArray();
    }

    /**
     * @return array|Collector
     */
    public static function protectedName()
    {
        $Instance = self::getInstance();
        return $Instance->getProtectedName();
    }

    /**
     * @return null|string
     */
    final public function getCurrentName()
    {
        return self::$currentKey;
    }

    public static function currentName()
    {
        $Instance = self::getInstance();
        return $Instance->getCurrentName();
    }

    /**
     * @return array
     */
    final public function getKeys()
    {
        return $this
            ->getObjectData()
            ->getKeys();
    }

    /**
     * @return array
     */
    public static function keys()
    {
        $Instance = self::getInstance();
        return $Instance->getKeys();
    }

    /**
     * @return array
     */
    final public function getAll()
    {
        return $this
            ->getObjectData()
            ->toArray();
    }

    /**
     * @return array
     */
    public static function all()
    {
        $Instance = self::getInstance();
        return $Instance->getAll();
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    final public function get($key, $default = null)
    {
        return $this->getDependency($key, $default);
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
        return $this->has($offset);
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
        return $this->get($offset);
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
        return $this->set($offset, $value);
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
        return $this->remove($offset);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        if (!$this->hasProtected($name) && is_object($value)) {
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
        return new ArrayIterator($this->getObjectData());
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
        if ($this->has($name)) {
            return $this->getDependency($name);
        }
        return property_exists($this, $name) ? $this->$name : null;
    }

    /**
     * [NOTE] arguments will be ignored!
     *
     * @param string $name
     * @param array  $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if ($this->has($name)) {
            return $this->getDependency($name);
        }
        throw new \Exception(
            sprintf(
                'Call to undefined method %s',
                $name
            )
        );
    }

    /**
     * Magic Method Calling Static Method
     * [NOTE] arguments will be ignored!
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        if (self::exist($name)) {
            return self::dependency($name);
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
