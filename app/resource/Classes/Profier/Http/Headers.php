<?php
namespace Pentagonal\Profier\Http;

use Pentagonal\Profier\Collector;
use Pentagonal\Profier\Interfaces\Http\HeadersInterface;

/**
 * Class Headers
 * @package Pentagonal\Profier\Http
 */
class Headers extends Collector implements HeadersInterface
{
    /**
     * Special HTTP headers that do not have the "HTTP_" prefix
     *
     * @var array
     */
    protected static $special = [
        'CONTENT_TYPE' => 1,
        'CONTENT_LENGTH' => 1,
        'PHP_AUTH_USER' => 1,
        'PHP_AUTH_PW' => 1,
        'PHP_AUTH_DIGEST' => 1,
        'AUTH_TYPE' => 1,
    ];

    /**
     * Create new headers collection with data extracted from
     * the application Environment object
     *
     * @param Environment $environment The Slim application Environment
     *
     * @return self
     */
    public static function createFromEnvironment(Environment $environment)
    {
        $data = [];
        foreach ($environment as $key => $value) {
            $key = strtoupper($key);
            if (isset(static::$special[$key]) || strpos($key, 'HTTP_') === 0) {
                if ($key !== 'HTTP_CONTENT_LENGTH') {
                    $data[$key] =  $value;
                }
            }
        }

        return new static($data);
    }

    /**
     * @param int|string $key
     * @return mixed|null
     */
    public function remove($key)
    {
        return parent::remove($this->normalizeKey($key));
    }

    /**
     * Add HTTP header value
     *
     * This method appends a header value. Unlike the set() method,
     * this method _appends_ this new value to any values
     * that already exist for this header name.
     *
     * @param string       $key   The case-insensitive header name
     * @param array|string $value The new header value(s)
     */
    public function addHeader($key, $value)
    {
        $oldValues = $this->retrieve($key, []);
        $newValues = is_array($value) ? $value : [$value];
        $this->set($key, array_merge($oldValues, array_values($newValues)));
    }

    public function __construct(array $elements = array())
    {
        parent::__construct();
        foreach ($elements as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Set HTTP header value
     *
     * This method sets a header value. It replaces
     * any values that may already exist for the header name.
     *
     * @param string $key   The case-insensitive header name
     * @param string $value The header value
     */
    public function set($key, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        parent::set($this->normalizeKey($key), [
            'value' => $value,
            'originalKey' => $key
        ]);
    }

    /**
     * Get HTTP header key as originally specified
     *
     * @param  string   $key     The case-insensitive header name
     * @param  mixed    $default The default value if key does not exist
     *
     * @return string
     */
    public function getOriginalKey($key, $default = null)
    {
        if ($this->has($key)) {
            return parent::get($this->normalizeKey($key))['originalKey'];
        }

        return $default;
    }

    /**
     * Does this collection have a given header?
     *
     * @param  string $offset The case-insensitive header name
     *
     * @return bool
     */
    public function has($offset)
    {
        return parent::has($this->normalizeKey($offset));
    }

    /**
     * Return array of HTTP header names and values.
     * This method returns the _original_ header name
     * as specified by the end user.
     *
     * @return array
     */
    public function all()
    {
        $all = $this->toArray();
        $out = [];
        foreach ($all as $key => $props) {
            $out[$props['originalKey']] = $props['value'];
        }

        return $out;
    }

    /**
     * Get HTTP header value
     *
     * @param  string  $key     The case-insensitive header name
     *
     * @return string|null
     */
    public function get($key)
    {
        return $this->retrieve($key);
    }

    /**
     * Get HTTP header value
     *
     * @param  string  $key     The case-insensitive header name
     * @param  mixed   $default The default value if key does not exist
     *
     * @return string|null
     */
    public function retrieve($key, $default = null)
    {
        if ($this->has($key)) {
            return parent::get($key)['value'];
        }
        return $default;
    }

    /**
     * Normalize header name
     *
     * This method transforms header names into a
     * normalized form. This is how we enable case-insensitive
     * header names in the other methods in this class.
     *
     * @param  string $key The case-insensitive header name
     *
     * @return string Normalized header name
     * @throws \InvalidArgumentException
     */
    public function normalizeKey($key)
    {
        if (is_string($key)) {
            $key = strtr(strtolower($key), '_', '-');
            if (strpos($key, 'http-') === 0) {
                $key = substr($key, 5);
            }
        } elseif (!is_int($key)) {
            throw new \InvalidArgumentException(
                'Keyname must be as a string',
                E_USER_ERROR
            );
        }

        return $key;
    }

    /**
     * @param int|string $key
     * @return bool
     */
    public function containsKey($key)
    {
        $key = $this->normalizeKey($key);
        return parent::contains($key);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return bool|void
     */
    public function offsetSet($offset, $value)
    {
        $offset = $this->normalizeKey($offset);
        return parent::offsetSet($offset, $value);
    }
}
