<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use AsceticSoft\Waypoint\Router;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use WaypointBench\RouteSet\RouteDefinition;
use WaypointBench\Support\SimpleContainer;

final class WaypointAdapter implements AdapterInterface
{
    private Router $router;
    private Psr17Factory $psr17Factory;

    public function getName(): string
    {
        return 'Waypoint';
    }

    public function initialize(): void
    {
        $container = new SimpleContainer();
        $this->psr17Factory = new Psr17Factory();
        $this->router = new Router($container);
    }

    public function registerRoutes(array $routes): void
    {
        $factory = $this->psr17Factory;

        foreach ($routes as $route) {
            $handler = $route->handler;
            $closure = static function () use ($factory, $handler) {
                $response = $factory->createResponse(200);
                $response->getBody()->write($handler);

                return $response;
            };

            $this->router->addRoute(
                path: $route->pattern,
                handler: $closure,
                methods: [$route->method],
            );
        }
    }

    public function dispatch(string $method, string $uri): string
    {
        $request = new ServerRequest($method, $uri);
        $response = $this->router->handle($request);

        return (string) $response->getBody();
    }
}
