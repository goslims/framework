<?php
namespace SLiMS\Http\Router;

use SLiMS\Http\Request;
use SLiMS\Http\Response;
use Symfony\Component\HttpKernel;
use Symfony\Component\Routing;
use Symfony\Component\Routing\RouteCollection;

class Router
{
    private $context;
    private $request;
    private $routes;
    private $matcher;

    public function __construct(Request $request)
    {
       $this->request = $request;
       $this->context = new Routing\RequestContext;
       $this->context->fromRequest($this->request);
    }

    public function getRoutes(): Router
    {
        $routes = array_diff(scandir($path = SB . 'routes' . DS), ['.', '..']);

        foreach ($routes as $route) {
            if (preg_match('/\.php/i', $route)) include $path . $route;
        }
        $this->routes = Route::getCollection();

        return $this;
    }

    public function handle()
    {
        $this->matcher = new Routing\Matcher\UrlMatcher($this->routes, $this->context);
        $controllerResolver = new HttpKernel\Controller\ControllerResolver();
        $argumentResolver = new HttpKernel\Controller\ArgumentResolver();


        try {
            $this->request->attributes->add($this->matcher->match($this->request->getPathInfo()));
        
            $controller = $controllerResolver->getController($this->request);
            $arguments = $argumentResolver->getArguments($this->request, $controller);

            $response = call_user_func_array($controller, $arguments);
        } catch (Routing\Exception\ResourceNotFoundException $exception) {
            $response = new Response('Not Found', 404);
        } catch (Exception $exception) {
            $response = new Response('An error occurred', 500);
        }
        
        $response->send();
    }
}