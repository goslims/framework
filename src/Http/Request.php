<?php
namespace SLiMS\Http;

use SLiMS\Utility\Json;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest {
    private static $methodAlias = [
        'input' => 'request',
        'headers' => 'headers',
        'raw' => 'getContent'
    ];

    private static $globalRequest = [
        'query','request','attributes',
        'cookies','files','server', 'content'
    ];

    public function __construct()
    {
        parent::__construct($_GET,
        $_POST,
        [],
        $_COOKIE,
        $_FILES,
        $_SERVER);
    }

    public static function capture()
    {
        self::createFromGlobals();
        return new static;
    }

    public function cookie(string $key, $default = null)
    {
        return $this->cookies($key)??$default;
    }

    public function has()
    {
        $result = [];
        foreach(func_get_args() as $key => $arg) {
            foreach (self::$globalRequest as $globalRequest) {
                if (is_null($this->$globalRequest) || is_null($request = $this->$globalRequest->get($arg))) continue;
                $result[$key] = $request;
            }
        }

        return func_num_args() === 1 ? ($result[0]??false) : (count($result) ? $result : false);
    }

    public function filled()
    {
        $result = $this->has(...func_get_args());

        if (is_bool($result) && !$result) return false;

        if (is_string($result) && !empty($result)) return true;

        return count(array_filter($result, fn($item) => !empty($item))) === func_num_args();
    }

    public function ip()
    {
        return $this->getClientIp();
    }

    public function inJson()
    {
        return $this->headers('Content-Type') === 'application/json' || $this->headers('Accept') === 'application/json';
    }

    public function json(string $key = '')
    {
        if (!$contentType = ($this->headers('Content-Type') || $this->headers('Content-Type') || $this->headers('Accept') || $this->headers('accept'))) return;

        $data = Json::parse($this->raw(), error: true);

        if (!empty($key)) {
            $paths = array_dot($key);
            $data = $data->toArray();
            $result = [];
            foreach ($paths as $order => $path) {
                if ($order < 1) {
                    $result = $data[$path] ?? null;
                    continue;
                }
                
                if (isset($result[$path])) {
                    $result = $result[$path];
                } else {
                    $result = null;
                }
            }

            return $result;
        }

        return $data;
    }

    public function __call($methodAsProp, $params)
    {
        $methodAsProp = self::$methodAlias[$methodAsProp]??$methodAsProp;

        if (property_exists($this, $methodAsProp)) {
            // get only instance
            if (!$params) return $this->$methodAsProp->all();
            return  count($params) === 1 ? 
                        // get single result
                        $this->$methodAsProp->get(...$params) : 

                        // multiple result
                        array_map(function($param) use($methodAsProp) {
                            return $this->$methodAsProp->get($param);
                        }, $params);
        }

        if (method_exists($this, $methodAsProp)) return $this->$methodAsProp();
    }

    public function __get($key)
    {
        return $this->input($key)??$this->query($key);
    }
}