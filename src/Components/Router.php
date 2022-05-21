<?php


namespace Algvo\Router\Components;

use Algvo\Router\Exceptions\BadRequestException;
use Algvo\Router\Exceptions\NotFoundException;/**
 * Класс Routing. Помогает проверить есть ли в системе вызываемый класс и открыть его
 * @author AlGvo <dp161185gav@gmail,com>
 * @version 1.0
 * @package Artpix
 */
class Router implements \Aigletter\Contracts\Routing\RouteInterface
{

    /**
     * @var string
     * @access private
     */
    private string $namespace;

    /**
     * Конструктор класса Routing
     * @param string $namespace
     * @access public
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Метод определения рредиректа
     *
     * Метод с помощью которого из URI определяем класс который нужно найти и метод который нужно вызвать.
     * Так же внутри вызывается метод resolveParameters которій определяет переданні ли необходиміе параметрі
     * @param string $uri
     * @return callable
     * @access public
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function route(string $uri): callable
    {
        if (str_contains($uri, '?')){
            $uri = substr($uri, 0, strpos($uri, "?"));
        }

        /** Разделяем uri на вызываемый класс и метод
         * @var array $segments
         */
        $segments = explode("/", $uri);

        array_shift($segments);

        $controller = $this->namespace.ucfirst($segments[0]);
        $method = $segments[1];

        if (!class_exists($controller) || !isset($method)) {
            throw new NotFoundException();
        }
        if (!method_exists($controller, $method)) {
            throw new BadRequestException();
        }
        $controller = new $controller();

        return function () use ($controller, $method) {
            $reflectionMethod = new \ReflectionMethod($controller, $method);
            $arguments = $this->resolveParameters($reflectionMethod);
            $reflectionMethod->invokeArgs($controller, $arguments);
        };
    }

    /**
     * Рефлекшен метод который позволяет найти какие параметры (переменные) принимает переданный метод
     *
     * Методом перебора проверяем есть ли в нашем GET запросе параметры которые ожидает заданный метод
     * Все найденные параметры сохраняются в отдельный массив, который возвращается из этой функции
     *
     * @param $reflectionMethod
     * @return array
     * @access protected
     */
    protected function resolveParameters($reflectionMethod): array
    {
        $arguments = [];
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