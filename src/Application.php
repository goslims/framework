<?php
namespace SLiMS;

// use Spatie\Ignition\Ignition;

class Application
{
    private static $instance = null;
    private string $init_path = '';
    private string $base_path = '';
    private array $constants = [];
    private array $properties = [];

    private function __construct(string $path){
        $this->init_path = dirname($path);
        $this->base_path = dirname($path, 2);
    }

    public static function getInstance()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        if (self::$instance === null) self::$instance = new Application($trace['file']);
        return self::$instance;
    }

    public function setConstants(array $constants)
    {
        $this->constants = array_merge($this->constants, $constants);
        foreach($this->constants as $constant => $value) {
            if (!defined($constant)) define($constant, $value);
        }
    }

    public function startUp()
    {
        $this->properties = require $this->getPath('base') . '/config/app.php';
        $this->setConstants(require $this->getPath('base') . '/config/constant.php');
    }

    public function withDatabase()
    {
        return file_exists($config_path = $this->getPath('base') . '/config/database.php');
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getPath(string $type)
    {
        return property_exists($this, $type . '_path') ? $this->{$type . '_path'} : null;
    }

    public function __get(string $propertyName)
    {
        return $this->properties[$propertyName]??null;
    }

    public function __isset(string $propertyName)
    {
        return isset($this->properties[$propertyName]);
    }
}