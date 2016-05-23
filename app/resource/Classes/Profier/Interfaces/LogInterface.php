<?php
namespace Pentagonal\Profier\Interfaces;

interface LogInterface
{
    public function info($object, $context = []);
    public function debug($object, $context = []);
    public function notice($object, $context = []);
    public function warning($object, $context = []);
    public function error($object, $context = []);
    public function critical($object, $context = []);
    public function alert($object, $context = []);
    public function emergency($object, $context = []);
    public function save();
    public function clear();
}
