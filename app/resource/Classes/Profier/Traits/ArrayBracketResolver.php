<?php
namespace Pentagonal\Profier\Traits;

use Pentagonal\Profier\Collector;

/**
 * Trait Class ArrayBracketResolver
 * @package Pentagonal\Profier\Traits
 */
trait ArrayBracketResolver
{
    /**
     * Data collection
     *
     * @var Collector
     */
    protected $data;

    /**
     * @param array $args
     */
    protected function setData(array $args = [])
    {
        $this->data = new Collector($args);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data->toArray();
    }

    /**
     * Fetch from array
     * Internal method used to retrieve values from global arrays.
     * alias of fetchFromArray();
     *
     * @param   mixed   $index   Index for item to be fetched from $array
     * @param   mixed   $default Default return if not exist
     * @return  mixed
     */
    public function fetch($index = null, $default = null)
    {
        return $this->fetchFromArray($index, $default);
    }

    /**
     * Fetch from array
     *
     * Internal method used to retrieve values from global arrays.
     *
     * @param   mixed   $index   Index for item to be fetched from $array
     * @param   mixed   $default Default return if not exist
     * @return  mixed
     */
    protected function fetchFromArray($index = null, $default = null)
    {
        $array = $this->getData();

        // If $index is NULL, it means that the whole $array is requested
        isset($index) || $index = array_keys($array);

        // allow fetching multiple keys at once
        if (is_array($index)) {
            $output = array();
            foreach ($index as $key) {
                $output[$key] = $this->fetchFromArray($key);
            }

            return $output;
        }

        if (isset($array[$index])) {
            $value = $array[$index];
        } elseif (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $index, $matches)) > 1) {
            // Does the index contain array notation
            $value = $array;
            for ($i = 0; $i < $count; $i++) {
                $key = trim($matches[0][$i], '[]');
                // Empty notation will return the value as array
                if ($key === '') {
                    break;
                }

                if (isset($value[$key])) {
                    $value = $value[$key];
                } else {
                    return $default;
                }
            }
        } else {
            return $default;
        }

        return $value;
    }
}
