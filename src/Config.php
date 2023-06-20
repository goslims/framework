<?php
namespace SLiMS;

use SLiMS\Database\QueryBuilder;

/**
 * @author : Waris Agung Widodo (ido.alit@gmail.com)
 * @date   : 2020-11-28  20:19:45
 * @license : GPL-3.0
 */
class Config
{
    private static $instance = null;
    private $configs = [];

    public function __construct()
    {
        // load default config folder
        $this->load(CONFIG_PATH, ['env.php', 'env.sample.php']);
    }

    /**
     * Get instance of this class
     *
     * @return static|null
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new static();
        return self::$instance;
    }

    /**
     * Load configuration files
     *
     * @param $directory
     * @param array $ignore
     */
    function load($directory, $ignore = [])
    {
        $ignore = array_unique(array_merge(['..', '.', 'index.html', 'index.php'], $ignore));
        $scanned_directory = array_diff(scandir($directory), $ignore);
        foreach ($scanned_directory as $file) {
            if (strpos($file, '.php')) {
                $file_path = $directory . DIRECTORY_SEPARATOR . $file;
                $this->configs[basename($file_path, '.php')] = require $file_path;
            }
        }

        // load config from database
        // this will override config file
        $this->loadFromDatabase();
    }

    /**
     * Load app preferences from database
     */
    function loadFromDatabase()
    {
        if (Application::getInstance()->withDatabase() === false) return;
        
        $data = QueryBuilder::table('setting')->select('setting_name', 'setting_value')->get();

        foreach ($data as $item) {
            $value = @unserialize($item->setting_value);
            if (is_array($value)) {
                foreach ($value as $id => $current_value) {
                    $this->configs[$item->setting_name][$id] = $current_value;
                }
            } else {
                $this->configs[$item->setting_name] = stripslashes($value??'');
            }
        }
    }

    /**
     * Get config with dot separator
     *
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $config = $default;
        foreach ($keys as $index => $_key) {
            if ($index < 1) {
                $config = $this->configs[$_key] ?? $default;
                continue;
            }
            if ($config === $default) break;
            if (isset($config[$_key])) {
                $config = $config[$_key];
            } else {
                $config = $default;
            }
        }

        // if result is null, try to get global $sysconf
        if (is_null($config)) $config = $this->getGlobal($key, $default);

        return $config;
    }

    /**
     * Get data with dot separator
     *
     * @param string $key
     * @param stirng $default
     * @return array|null
     */
    public function getGlobal($key, $default = null)
    {
        global $sysconf;
        $keys = explode('.', $key);
        $config = $default;
        foreach ($keys as $index => $_key) {
            if ($index < 1) {
                $config = $sysconf[$_key] ?? $default;
                continue;
            }
            if ($config === $default) break;
            if (isset($config[$_key])) {
                $config = $config[$_key];
            } else {
                $config = $default;
            }
        }
        return $config;
    }

    /**
     * Get config as plain text
     */
    public static function getFile(string $filename)
    {
        return file_exists($path = SB . 'config/' . $filename . '.php') ? file_get_contents($path) : null;
    }

    /**
     * Create some configuration file
     * into <slims-root>/config/
     *
     * @param string $filename
     * @param string $content
     * @return void
     */
    public static function create(string $filename, $content = '')
    {
        if (is_callable($content)) $content = $content($filename);
        file_put_contents(SB . 'config/' . basename($filename) . '.php', $content);
    }

    /**
     * Create or update SLiMS configuration
     * to database
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public static function createOrUpdate(string $name, $value)
    {
        
    }
}