<?php
namespace Pentagonal\Profier\Exceptions;

/**
 * Class ProfierException
 * @package Pentagonal\Profier\Exceptions
 */
class ProfierException extends \Exception
{
    protected $message;
    protected $code;
    protected $file;
    protected $line;

    public function setLine($line)
    {
        if (is_numeric($line)) {
            $line = abs($line);
            if (is_int($line)) {
                $this->line = $line;
            }
        }
    }

    public function setFile($file)
    {
        if (is_string($file) && is_file($file)) {
            $this->file = $file;
        }
    }

    public function setCode($code)
    {
        if (is_numeric($code)) {
            $code = abs($code);
            if (is_int($code)) {
                $this->code = $code;
            }
        }
    }
}
