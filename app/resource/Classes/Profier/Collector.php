<?php
namespace Pentagonal\Profier;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Collector
 * @package Pentagonal\Profier
 */
class Collector extends ArrayCollection
{
    /**
     * Check element if key exists
     *
     * @param string|integer $offset The key/index of the element to retrieve.
     * @return bool
     */
    public function has($offset)
    {
        return $this->containsKey($offset);
    }

    /**
     * Replace or add if not exists
     *
     * @param array $values
     */
    public function replace(array $values)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Gets the element at the specified key/index.
     *
     * @param string|integer $key     The key/index of the element to retrieve.
     * @param mixed          $default Default return if not exists
     *
     * @return mixed
     */
    public function retrieve($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        return $default;
    }
}
