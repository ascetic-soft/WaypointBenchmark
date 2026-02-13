<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use Phroute\Phroute\Dispatcher;
use Phroute\Phroute\RouteCollector;
use WaypointBench\RouteSet\RouteDefinition;

final class PHRouteAdapter implements AdapterInterface
{
    private RouteCollector $collector;
    private Dispatcher $dispatcher;

    public function getName(): string
    {
        return 'PHRoute';
    }

    public function initialize(): void
    {
        $this->collector = new RouteCollector();
    }

    public function registerRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            $handler = $route->handler;

            // PHRoute passes route parameters as positional args to the handler
            $this->collector->addRoute(
                $route->method,
                $route->pattern,
                static fn (mixed ...$args) => $handler,
            );
        }

        $this->dispatcher = new Dispatcher($this->collector->getData());
    }

    public function dispatch(string $method, string $uri): string
    {
        return $this->dispatcher->dispatch($method, $uri);
    }
}
