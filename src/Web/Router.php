<?php

namespace Soli\Web;

use Soli\RouterInterface;
use Soli\RouterTrait;

use Exception;

class Router implements RouterInterface
{
    use RouterTrait;

    protected $routes = [];

    /**
     * @var \FastRoute\Dispatcher
     */
    protected $dispatcher;

    public function __construct()
    {
        $this->defaultNamespaceName = "App\\Controllers\\";

        // default routes
        $this->map('/', [], '*');
    }

    /**
     * Load routes config.
     *
     * @param array $routesConfig
     * @return array
     */
    public function load(array $routesConfig)
    {
        foreach ($routesConfig as $route) {
            $pattern = $route[0];
            $handler = $route[1] ?? null;
            $methods = $route[2] ?? '*';
            $this->map($pattern, $handler, $methods);
        }
        return $this->routes;
    }

    /**
     * Add route.
     *
     * @param string $pattern
     * @param array|string $handler
     * @param string|string[] $methods 'GET' or ['GET', 'POST']
     */
    public function map($pattern, $handler = null, $methods = '*')
    {
        $this->routes[$pattern] = [
            'handler' => $handler,
            'methods' => $methods,
        ];
    }

    // @codeCoverageIgnoreStart
    public function get($pattern, $handler)
    {
        $this->map($pattern, $handler, 'GET');
    }

    public function post($pattern, $handler)
    {
        $this->map($pattern, $handler, 'POST');
    }

    public function put($pattern, $handler)
    {
        $this->map($pattern, $handler, 'PUT');
    }

    public function delete($pattern, $handler)
    {
        $this->map($pattern, $handler, 'DELETE');
    }

    public function head($pattern, $handler)
    {
        $this->map($pattern, $handler, 'HEAD');
    }

    public function trace($pattern, $handler)
    {
        $this->map($pattern, $handler, 'TRACE');
    }

    public function options($pattern, $handler)
    {
        $this->map($pattern, $handler, 'OPTIONS');
    }
    // @codeCoverageIgnoreEnd

    public function setDefaults(array $defaults)
    {
        if (isset($defaults['namespace'])) {
            $this->defaultNamespaceName = $defaults['namespace'];
        }
        if (isset($defaults['controller'])) {
            $this->defaultHandlerName = $defaults['controller'];
        }
        if (isset($defaults['action'])) {
            $this->defaultActionName = $defaults['action'];
        }
        if (isset($defaults['params'])) {
            $this->defaultParams = $defaults['params'];
        }

        return $this;
    }

    /**
     * @param string $uri
     * @throws Exception
     */
    public function dispatch($uri = null)
    {
        $httpMethod = $this->getRequestMethod();
        if (empty($uri)) {
            $uri = $this->getRewriteUri();
        }

        $this->handleDispatcherResponse(
            $this->createDispatcher()->dispatch($httpMethod, $uri),
            $httpMethod,
            $uri
        );
    }

    /**
     * Create a FastRoute dispatcher instance for the application.
     *
     * @return \FastRoute\Dispatcher
     */
    protected function createDispatcher()
    {
        return $this->dispatcher ?: \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            foreach ($this->routes as $pattern => $route) {
                $r->addRoute($route['methods'], $pattern, $route['handler']);
            }
        });
    }

    /**
     * Handle the response from the FastRoute dispatcher.
     *
     * @param array $routeInfo
     * @param string $httpMethod
     * @param string $uri
     * @throws Exception
     */
    protected function handleDispatcherResponse($routeInfo, $httpMethod, $uri)
    {
        if ($routeInfo[0] == \FastRoute\Dispatcher::NOT_FOUND) {
            throw new Exception("Not found handler: $uri", 404);
        }
        if ($routeInfo[0] == \FastRoute\Dispatcher::METHOD_NOT_ALLOWED) {
            $allowed = (array)$routeInfo[1];
            throw new Exception("Method Not Allowed: $uri, allowed: " . implode(',', $allowed), 405);
        }

        // \FastRoute\Dispatcher::FOUND
        $this->handleFoundRoute($routeInfo);
    }

    /**
     * Handle a route found by the dispatcher.
     *
     * @param  array  $routeInfo
     */
    protected function handleFoundRoute($routeInfo)
    {
        $routeInfo = $this->formatRouteInfo($routeInfo);

        $handler = $routeInfo[1];
        $params  = $routeInfo[2];

        // 存储控制器、方法及参数
        if ($handler['namespace']) {
            $this->namespaceName = $handler['namespace'];
        }

        if ($handler['controller']) {
            $this->handlerName = $handler['controller'];
        }

        if ($handler['action']) {
            $this->actionName = $handler['action'];
        }

        $this->params = $params;
    }

    protected function formatRouteInfo($routeInfo)
    {
        $handler = $routeInfo[1];
        $params  = $routeInfo[2];

        // $router->map('/users/{id}', 'user::update', 'PUT');
        // $router->map('/users/{id}', 'App\Controllers\User::update', 'PUT');
        if (is_string($handler)) {
            $tmp = explode('::', $handler);
            $handler = [
                'controller' => $tmp[0] ?? null,
                'action'     => $tmp[1] ?? null,
            ];
        }

        $routeInfo[1] = [
            'namespace'  => $handler['namespace'] ?? $params['namespace'] ?? null,
            'controller' => $handler['controller'] ?? $params['controller'] ?? null,
            'action'     => $handler['action'] ?? $params['action'] ?? null,
        ];

        unset($params['namespace']);
        unset($params['controller']);
        unset($params['action']);

        $routeInfo[2] = $params;

        return $routeInfo;
    }

    protected function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'] ?? '';
    }

    protected function getRewriteUri()
    {
        $uri = $_GET['_uri'] ?? rawurldecode($_SERVER['REQUEST_URI']);
        // 去除 query string
        list($uri) = explode('?', $uri);

        return $uri ?: '/';
    }
}
