<?php


namespace Algvo\Router\Components;


class Router implements \Aigletter\Contracts\Routing\RouteInterface
{


    private string $namespace;

    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    public function route(string $uri): callable
    {
        $segments = explode("/", $uri);
        array_shift($segments);
        $controller = $this->namespace. ucfirst($segments[0]);
        $method = $segments[1];

        if (class_exists($controller) && isset($method) && method_exists($controller, $method)) {
            return function () use ($controller, $method) {
               // return call_user_func([$controller, $method]);
                $callback = [$controller, $method];
                $id = $_GET['id'];
                $callback($id);
            };
        }

        http_response_code(404);
        echo '404 Not found';
        die();
    }
}