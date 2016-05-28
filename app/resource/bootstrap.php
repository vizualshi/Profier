<?php
/**
 * PSR0 autoloader
 */
return spl_autoload_register(function ($className) {
    $className = ltrim($className, '\\');
    $nameSpace = 'Pentagonal\\Profier\\';
    $baseDir = __DIR__ . '/Classes/Profier/';
    if (stripos($className, $nameSpace) !== 0) {
        // continue to next auto loader
        return;
    }
    $classFile = str_replace('\\', '/', substr($className, strlen($nameSpace)));
    $classFile = $baseDir . $classFile . '.php';
    if (file_exists($classFile)) {
        /**
         * Call stack
         */
        call_user_func(function () {
            require_once func_get_arg(0);
        }, $classFile);
    }
}, true);
