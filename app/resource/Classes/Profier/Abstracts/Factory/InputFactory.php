<?php
namespace Pentagonal\Profier\Abstracts\Factory;

use Pentagonal\Profier\Collector;
use Pentagonal\Profier\Component\ArrayStringParser;
use Pentagonal\Profier\Http\Environment;
use Pentagonal\Profier\Http\Headers;

/**
 * Abstract Class InputFactory
 * @package Pentagonal\Profier\Abstracts\Factory
 */
abstract class InputFactory
{
    private $record;
    private $request_method;

    public function __construct()
    {
        if (!isset($_ENV) || !is_array($_ENV)) {
            // resetting Environment
            global $_ENV;
            $_ENV = [];
        }

        $headers = array();
        foreach (Headers::createFromEnvironment(Environment::create($_SERVER)) as $item) {
            $headers[$item['originalKey']] = reset($item['value']);
        };

        $this->record = new Collector(
            [
                '_SERVER' => new ArrayStringParser($_SERVER),
                '_REQUEST'=> new ArrayStringParser($_REQUEST),
                '_POST'   => new ArrayStringParser($_POST),
                '_GET'    => new ArrayStringParser($_GET),
                '_FILES'  => new ArrayStringParser($_FILES),
                '_COOKIE' => new ArrayStringParser($_COOKIE),
                '_ENV'    => new ArrayStringParser($_ENV),
                '_HEADER'  => new ArrayStringParser($headers),
            ]
        );

        $this->request_method = $this->server('REQUEST_METHOD');
        //put is has alternative of post
        if (is_string($this->request_method) && $this->request_method) {
            if ($this->request_method == 'PUT') {
                $content = @file_get_contents('php://input');
                if (is_array($content)) {
                    parse_str($content, $_POST);
                    $this->record->get('_POST')->replace($_POST);
                }
            } elseif (in_array($this->request_method, ['CONNECT', 'PATCH', 'HEAD', 'DELETE', 'OPTIONS', 'TRACE'])) {
                $content = file_get_contents('php://input');
                if (is_array($content)) {
                    parse_str($content, $input);
                    $this->record->set($this->request_method, $input);
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getRequestMethod()
    {
        return $this->request_method;
    }

    /**
     * Getting Post
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function post($keyname = null, $default = null)
    {
        return $this
            ->record
            ->get('_POST')
            ->fetch($keyname, $default);
    }

    /**
     * Getting Request
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function request($keyname = null, $default = null)
    {
        return $this
            ->record
            ->get('_REQUEST')
            ->fetch($keyname, $default);
    }

    /**
     * Getting Get
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function get($keyname = null, $default = null)
    {
        return $this
            ->record
            ->get('_GET')
            ->fetch($keyname, $default);
    }

    /**
     * Getting Put (as post)
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function put($keyname = null, $default = null)
    {
        return $this->post($keyname, $default);
    }

    /**
     * Getting Connect
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function connect($keyname = null, $default = null)
    {
        if (!$this->record->has('CONNECT')) {
            return $default;
        }

        return $this
            ->record
            ->get('CONNECT')
            ->fetch($keyname, $default);
    }

    /**
     * Getting Connect
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function head($keyname = null, $default = null)
    {
        if (!$this->record->has('HEAD')) {
            return $default;
        }

        return $this
            ->record
            ->get('HEAD')
            ->fetch($keyname, $default);
    }

    /**
     * Getting Delete
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function delete($keyname = null, $default = null)
    {
        if (!$this->record->has('DELETE')) {
            return $default;
        }

        return $this
            ->record
            ->get('DELETE')
            ->fetch($keyname, $default);
    }

    /**
     * Getting Patch
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function patch($keyname = null, $default = null)
    {
        if (!$this->record->has('PATCH')) {
            return $default;
        }

        return $this
            ->record
            ->get('PATCH')
            ->fetch($keyname, $default);
    }

    /**
     * Getting Trace
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function trace($keyname = null, $default = null)
    {
        if (!$this->record->has('TRACE')) {
            return $default;
        }

        return $this
            ->record
            ->get('TRACE')
            ->fetch($keyname, $default);
    }

    /**
     * Getting Patch
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function options($keyname = null, $default = null)
    {
        if (!$this->record->has('OPTIONS')) {
            return $default;
        }

        return $this
            ->record
            ->get('OPTIONS')
            ->fetch($keyname, $default);
    }

    /**
     * Getting Files
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function files($keyname = null, $default = null)
    {
        return $this
            ->record
            ->get('_FILES')
            ->fetch($keyname, $default);
    }

    /**
     * Getting Headers
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function header($keyname = null, $default = null)
    {
        /**
         * Normalize Keyname
         */
        if (is_string($keyname)) {
            $keyname = strtoupper($keyname);
            if (strpos($keyname, 'HTTP_') !== 0) {
                $keyname = 'HTTP_'.$keyname;
            }
        }
        return $this
            ->record
            ->get('_HEADER')
            ->fetch($keyname, $default);
    }

    /**
     * Getting Cookie
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function cookie($keyname = null, $default = null)
    {
        return $this
            ->record
            ->get('_COOKIE')
            ->fetch($keyname, $default);
    }

    /**
     * Getting Server
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function server($keyname = null, $default = null)
    {
        return $this
            ->record
            ->get('_SERVER')
            ->fetch($keyname, $default);
    }

    /**
     * Getting Environment
     *
     * @param mixed $keyname
     * @param mixed $default
     * @return mixed
     */
    public function env($keyname = null, $default = null)
    {
        return $this
            ->record
            ->get('_ENV')
            ->fetch($keyname, $default);
    }
}
