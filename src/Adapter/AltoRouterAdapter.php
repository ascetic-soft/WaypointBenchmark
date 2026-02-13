<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use AltoRouter;
use WaypointBench\RouteSet\RouteDefinition;

final class AltoRouterAdapter implements AdapterInterface
{
    private AltoRouter $router;

    public function getName(): string
    {
        return 'AltoRouter';
    }

    public function initialize(): void
    {
        $this->router = new AltoRouter();
    }

    public function registerRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            // AltoRouter uses [type:name] syntax, convert {name} to [:name]
            $pattern = preg_replace('/\{(\w+)\}/', '[:$1]', $route->pattern);

            $this->router->map(
                $route->method,
                $pattern,
                $route->handler,
            );
        }
    }

    public function dispatch(string $method, string $uri): string
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;

        $match = $this->router->match($uri, $method);

        if ($match === false) {
            throw new \RuntimeException("Route not found: {$method} {$uri}");
        }

        return $match['target'];
    }
}
