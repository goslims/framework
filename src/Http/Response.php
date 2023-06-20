<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-02-12 10:31:29
 * @modify date 2023-06-19 16:05:30
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Http;

use Exception;
use SLiMS\Json;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Response extends SymfonyResponse
{
    private static $instance = null;
    private static $commonResponseCode = [
        'success' => 200, 
        'badRequest' => 400, 'unAuthorized' => 401, 'forbidden' => 403,
        'notFound' => 404, 'methodNotAllow' => 405, 
        'phpError' => 500, 'badGateway' => 502, 'serviceUnvailable' => 503
    ];

    public function __construct()
    {
        parent::__construct(...func_get_args());
    }

    public static function getInstance()
    {
        if (self::$instance === null) self::$instance = new Response;
        return self::$instance;
    }

    public static function setHeaders(array $headers)
    {
        foreach($headers as $key => $content) 
            self::getInstance()->headers->set($key, $content);
    }

    public function html(string $html, int $statusCode = 200)
    {
        self::getInstance()->setContent($html);
        self::getInstance()->headers->set('Content-Type', 'text/html');
        self::getInstance()->setStatusCode($statusCode);
        return self::getInstance();
    }

    public function json(mixed $data, int $statusCode = 200)
    {
        self::getInstance()->setContent(Json::stringify($data));
        self::getInstance()->headers->set('Content-Type', 'application/json');
        self::getInstance()->setStatusCode($statusCode);
        return self::getInstance();
    }

    public function plain(string $character, int $statusCode = 200)
    {
        self::getInstance()->setStatusCode($statusCode);
        self::getInstance()->setContent($character);
        self::getInstance()->headers->set('Content-Type', 'text/plain');
        return self::getInstance();
    }

    public static function __callStatic($method, $params)
    {
        if (isset(self::$commonResponseCode[$method])) {
            self::getInstance()->setStatusCode(self::$commonResponseCode[$method]);
            return self::getInstance();
        } else if (!method_exists(__CLASS__, $method)) {
            throw new Exception("Error Processing Request : method $method not exists.", 1);
        }
    }

    public function __call($method, $params)
    {
        if (isset(self::$commonResponseCode[$method])) {
            self::getInstance()->setStatusCode(self::$commonResponseCode[$method]);
            return self::getInstance();
        } else if (!method_exists($this, $method)) {
            throw new Exception("Error Processing Request : method $method not exists.", 1);
        }
    }
}