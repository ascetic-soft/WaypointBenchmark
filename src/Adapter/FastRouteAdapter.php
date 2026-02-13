<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use FastRoute;
use WaypointBench\RouteSet\RouteDefinition;

final class FastRouteAdapter implements AdapterInterface
{
    private FastRoute\Dispatcher $dispatcher;

    /** @var RouteDefinition[] */
    private array $pendingRoutes = [];

    public function getName(): string
    {
        return 'FastRoute';
    }

    public function initialize(): void
    {
        $this->pendingRoutes = [];
    }

    public function registerRoutes(array $routes): void
    {
        $this->pendingRoutes = $routes;

        $this->dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) use ($routes): void {
            foreach ($routes as $route) {
                $r->addRoute($route->method, $route->pattern, $route->handler);
            }
        });
    }

    public function dispatch(string $method, string $uri): string
    {
        $result = $this->dispatcher->dispatch($method, $uri);

        return match ($result[0]) {
            FastRoute\Dispatcher::FOUND => $result[1],
            default => throw new \RuntimeException("Route not found: {$method} {$uri}"),
        };
    }
}
