<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use Nette\Routing\RouteList;
use Nette\Routing\Route;
use WaypointBench\RouteSet\RouteDefinition;

final class NetteAdapter implements AdapterInterface
{
    private RouteList $router;

    /** @var array<string, string> */
    private array $handlerMap = [];

    public function getName(): string
    {
        return 'Nette';
    }

    public function initialize(): void
    {
        $this->router = new RouteList();
        $this->handlerMap = [];
    }

    public function registerRoutes(array $routes): void
    {
        foreach ($routes as $i => $route) {
            // Nette uses <param> syntax instead of {param}
            $pattern = str_replace(['{', '}'], ['<', '>'], $route->pattern);

            $routeName = 'route_' . $i;
            $this->handlerMap[$routeName] = $route->handler;

            $this->router->addRoute($pattern, ['presenter' => $routeName]);
        }
    }

    public function dispatch(string $method, string $uri): string
    {
        // Nette uses an internal HTTP request for matching
        $httpRequest = new \Nette\Http\Request(
            new \Nette\Http\UrlScript('http://localhost' . $uri),
            method: $method,
        );

        $params = $this->router->match($httpRequest);

        if ($params === null) {
            throw new \RuntimeException("Route not found: {$method} {$uri}");
        }

        $routeName = $params['presenter'];

        return $this->handlerMap[$routeName];
    }
}
