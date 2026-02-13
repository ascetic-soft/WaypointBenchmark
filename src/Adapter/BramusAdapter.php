<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use Bramus\Router\Router;
use WaypointBench\RouteSet\RouteDefinition;

final class BramusAdapter implements AdapterInterface
{
    private Router $router;
    private string $lastResult = '';

    public function getName(): string
    {
        return 'Bramus';
    }

    public function initialize(): void
    {
        $this->router = new Router();
        $this->router->setBasePath('');
        $this->lastResult = '';
    }

    public function registerRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            $handler = $route->handler;

            $this->router->match(
                $route->method,
                $route->pattern,
                function () use ($handler): void {
                    $this->lastResult = $handler;
                },
            );
        }
    }

    public function dispatch(string $method, string $uri): string
    {
        $this->lastResult = '';

        // Bramus reads from $_SERVER, so we simulate it
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        ob_start();
        $this->router->run();
        ob_end_clean();

        if ($this->lastResult === '') {
            throw new \RuntimeException("Route not found: {$method} {$uri}");
        }

        return $this->lastResult;
    }
}
