<?php
namespace SLiMS\Http\Router;

use SLiMS\Http\Request;
use SLiMS\Http\Response;
use SLiMS\Http\ResponseEvent;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;


class Router
{
    private $context;
    private $request;
    private $routes;
    private $matcher;
    private $dispatcher;

    public function __construct(Request $request)
    {
       $this->request = $request;
       $this->context = new RequestContext;
       $this->dispatcher = new EventDispatcher;
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
        $this->matcher = new UrlMatcher($this->routes, $this->context);
        $controllerResolver = new ControllerResolver();
        $argumentResolver = new ArgumentResolver();


        $this->dispatcher->addListener('response', function (ResponseEvent $event) {
            $response = $event->getResponse();
            $request = $event->getRequest();
        
            if ($request->query('my_name'))
            {
                $response->json('Failed')->send();
                exit;
            }
        
            $response->setContent($response->getContent().'GA CODE');
        });

        try {
            $this->request->attributes->add($this->matcher->match($this->request->getPathInfo()));
        
            $controller = $controllerResolver->getController($this->request);
            $arguments = $argumentResolver->getArguments($this->request, $controller);
            $response = call_user_func_array($controller, $arguments);
        } catch (ResourceNotFoundException $exception) {
            $response = new Response('Not Found', 404);
        } catch (\SLiMS\Http\Exception\ErrorPage $e) {
            $response = new Response($e->getMessage(), 500);
        } catch (\Exception $exception) {
            $response = new Response('An error occurred', 500);
        }
        
        // dispatch a response event
        $this->dispatcher->dispatch(new ResponseEvent($response, $this->request), 'response');


        return $response;
    }
}