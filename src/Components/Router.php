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
        $uri = substr($uri, 0, strpos($uri, "?"));
        $segments = explode("/", $uri);
        array_shift($segments);
        $controller = $this->namespace. ucfirst($segments[0]);
        $method = $segments[1];
        if (class_exists($controller) && isset($method) && method_exists($controller, $method)) {
            $controller = new $controller();
            return function () use ($controller, $method) {
                $callback = [$controller, $method];
                $reflectionMethod = new \ReflectionMethod($controller, $method);
                $arguments = $this->resolveParameters($reflectionMethod);
                $reflectionMethod->invokeArgs($controller, $arguments);
            };
        }
        http_response_code(404);
        echo '404 Not found';
        die();
    }
    protected function resolveParameters($reflectionMethod)
    {

        foreach ($reflectionMethod->getParameters() as $parameters) {
            $name = $parameters->getName();
            $type = $parameters->getType();
            if  ($type && !$type->isBuiltin()) {
                $className = $type->getName();
                $value = new $className();
            } else {
                if (!isset($_GET[$name])) {
                    continue;
                }
                echo 'Name'. $name . PHP_EOL;
                $value = $_GET[$name];
                if ($type && $type->getName() !== gettype($value)) {
                    settype($value, $type->getName());
                }
            }
            $arguments[$name] = $value;
        }
        return $arguments;
    }
}