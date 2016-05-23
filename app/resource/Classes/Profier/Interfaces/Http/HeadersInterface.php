<?php
namespace Pentagonal\Profier\Interfaces\Http;

/**
 * Interface HeadersInterface
 * @package Pentagonal\Profier\Interfaces\Http
 */
interface HeadersInterface
{
    public function addHeader($key, $value);
    public function normalizeKey($key);
}
