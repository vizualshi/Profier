<?php
return [
    'path' => [
        'admin' => 'admin',
        'api'  => 'api',
    ],
    'db' => [
        'host' => 'localhost',
        'user' => 'root',
        'password' => 'mysql',
        'dbname' => 'chat',
        'driver' => 'pdo_mysql',
    ],
    'cookie' => [
        'name' => 'chat',
        'domain' => null,
        'expire' => null,
        'httponly' => null,
        'path' => '/',
    ],
    'security' => [
        'salt' => null,
        'hash' => null,
        'csrf' => true,
    ],
    'cache' => [
        'dir' => 'cache',
        'enable' => true,
    ]
];
