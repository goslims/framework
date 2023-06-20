<?php
namespace SLiMS;

use Closure;
use SLiMS\Http\Request;
use SLiMS\Http\Router\Router;
use SLiMS\Database\Connection;

class Kernel
{
    private $app;
    const HOOK_BEFORE = 1;
    const HOOK_AFTER = 2;
    private array $hooks = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function registerHookBeforeBoot(Closure $hook)
    {
        $this->hooks[self::HOOK_BEFORE][] = $hook;
    }

    public function registerHookAfterBoot(Closure $hook)
    {
        $this->hooks[self::HOOK_AFTER][] = $hook;
    }

    public function runHook(int $type)
    {
        foreach($this->hooks[$type]??[] as $hook) $hook();
    }

    public function boot(Closure|string $callback = '')
    {
        if (is_callable($callback)) $callback($this);
        $this->runHook(self::HOOK_BEFORE);
        $this->app->startUp();
        $this->loadDatabase();
        $this->loadConfig();
        $this->setEnv();
        $this->loadHelpers();
        $this->setTimezone();
        $this->setIgnition();
        $this->runHook(self::HOOK_BEFORE);
        return $this;
    }

    private function setEnv()
    {
        $env = \Dotenv\Dotenv::createImmutable(SB);
        $env->load();
    }

    private function setIgnition()
    {
        \SLiMS\Ignition::init()->register();
    }

    public function handle(Request $request)
    {
        if (isCli()){
            
        } else {
            $router = new Router($request);
            return $router->getRoutes()->handle();
        }
    }

    private function loadDatabase()
    {        
        if ($this->app->withDatabase())
        {
            $connection = new Connection;
            $databaseConfig = require SB . 'config/database.php';
            $connection->register($databaseConfig['connections'], $databaseConfig['default']);
            
            if ($this->app->database['autoloadDatabase']) $connection->setAsGlobal();
            if ($this->app->database['withOrm']) $connection->bootEloquent();
        }
    }

    private function loadHelpers()
    {
        include __DIR__ . '/helpers.php';
    }

    private function loadConfig()
    {
        Config::getInstance();
    }

    private function setTimezone()
    {
        @date_default_timezone_set(config('timezone', 'Asia/Jakarta'));
    }
}