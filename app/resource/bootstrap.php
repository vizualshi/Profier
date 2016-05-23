<?php
/**
 * PSR0 autoloader
 */
return spl_autoload_register(function ($className) {
    $baseDir = __DIR__ . '/Classes/Profier/';
    $prefix = 'Pentagonal\\Profier\\';
    // does the class use the namespace prefix?
    $len = strlen($prefix);
    $className = ltrim($className, '\\');
    if (strncmp($prefix, $className, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }
    // strip the string
    $className = substr($className, $len);
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = str_replace('\\', '/', $className);
        $namespace = substr($namespace, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        if (!is_dir($baseDir. $namespace . '/')) {
            // if no match
            return;
        }
        $baseDir .= $namespace . '/';
    }
    /**
     * Fix File for
     */
    if (file_exists($baseDir . $className . '.php')) {
        /** @noinspection PhpIncludeInspection */
        require_once($baseDir . $className . '.php');
    }
}, true);
