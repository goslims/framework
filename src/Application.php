<?php
namespace SLiMS;

class Application
{
    /**
     * @var null
     */
    private static $instance = null;

    /**
     * Intialisation path infomation
     *
     * @var string
     */
    private string $init_path = '';

    /**
     * Base path infomation
     *
     * @var string
     */
    private string $base_path = '';

    /**
     * @var array
     */
    private array $constants = [];

    /**
     * @var array
     */
    private array $properties = [];

    /*
     * Initialisation application instance
     *
     * @param string $path
     * @return void
     */
    private function __construct(string $path){
        $this->init_path = dirname($path);
        $this->base_path = dirname($path, 2);
    }

    public static function getInstance(): Application
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        if (self::$instance === null) self::$instance = new Application($trace['file']);
        return self::$instance;
    }

    /**
     * Register default SLIMS Constant
     *
     * @param array $constants
     * @return void
     */
    public function setConstants(array $constants): void
    {
        $this->constants = array_merge($this->constants, $constants);
        foreach($this->constants as $constant => $value) {
            if (!defined($constant)) define($constant, $value);
        }
    }

    /**
     * Start application defaut properties etc.
     *
     * @param Undocumented function value
     * @return void
     */
    public function startUp(): void
    {
        $this->properties = require $this->getPath('base') . '/config/app.php';
        $this->setConstants(require $this->getPath('base') . '/config/constant.php');
    }

    /**
     * At first installation the
     * system is not ready with database connection
     * return value as database existension
     *
     * @param Undocumented function value
     * @return bool
     */    
    public function withDatabase(): bool
    {
        return file_exists($config_path = $this->getPath('base') . '/config/database.php');
    }

    /**
     * @return array
     */
    public function getProperties(): array
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