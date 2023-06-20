<?php
namespace SLiMS\Http\Router;

use SLiMS\Http\Request;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

class Route
{
    private static $instance = null;
    private array $supportedMethods = ['GET','POST','PUT','PATCH','DELETE'];
    private $request;
    private $collection;
    private $method;
    private array $list = [];

    private function __construct(Request $request)
    {
        $this->collection = new RouteCollection;
        $this->request = $request;
        $this->method = $request->server('REQUEST_METHOD');
    }

    public static function getInstance()
    {
        if (self::$instance === null) self::$instance = new Route(Request::capture());
        return self::$instance;
    }

    public static function __callStatic($method, $parameters)
    {
        $instance = self::getInstance();
        $method = strtoupper($method);

        if (isset($instance->$method)) {
            $instance->list[$method][rand(1,1000)] = new SymfonyRoute(path: $parameters[0], defaults: ['_controller' => $parameters[1]], methods: $method);
        } else {
            throw new BadMethodCallException("Http method {$method} is not supported!");
        }

        return $instance;
    }

    public function name(string $name)
    {
        if (!isset($this->list[$this->method])) abort("Method {$this->method} is not exists!", 500);
        $lastKey = array_key_last($this->list[$this->method]);
        $this->list[$this->method][$name] = $this->list[$this->method][$lastKey];
        unset($this->list[$this->method][$lastKey]);
    }

    public static function getCollection()
    {
        foreach (self::getInstance()->list[self::getInstance()->method] as $name => $value) {
            self::getInstance()->collection->add($name, $value);
        }

        return self::getInstance()->collection;
    }

    public function __isset($name)
    {
        return in_array($name, $this->supportedMethods);
    }
}