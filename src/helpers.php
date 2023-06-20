<?php
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

if (!function_exists('config')) {
    /**
     * Helper to get config with dot separator keys
     *
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    function config($key, $default = null) {
        return \SLiMS\Config::getInstance()->get($key, $default);
    }
}

if (!function_exists('isCli')) 
{
    function isCli()
    {
        return php_sapi_name() === 'cli';
    }
}

if (!function_exists('app')) 
{
    function app()
    {
        return \SLiMS\Application::getInstance();
    }
}

if (!function_exists('route'))
{
    function route()
    {
        return \SLiMS\Http\Router\Route::getInstance();
    }
}

if (!function_exists('response'))
{
    function response()
    {
        return \SLiMS\Http\Response::getInstance();
    }
}

if (!function_exists('abort'))
{
    function abort(string $message, int $code)
    {
        throw new Symfony\Component\Routing\Exception\ResourceNotFoundException($message, $code);
    }
}