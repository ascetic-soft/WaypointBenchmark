<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use WaypointBench\RouteSet\RouteDefinition;

final class SymfonyAdapter implements AdapterInterface
{
    private RouteCollection $routeCollection;
    private UrlMatcher $matcher;
    private RequestContext $context;

    public function getName(): string
    {
        return 'Symfony';
    }

    public function initialize(): void
    {
        $this->routeCollection = new RouteCollection();
        $this->context = new RequestContext();
    }

    public function registerRoutes(array $routes): void
    {
        foreach ($routes as $i => $route) {
            // Convert {param} to {param} (Symfony uses the same syntax)
            $symfonyRoute = new Route(
                path: $route->pattern,
                defaults: ['_handler' => $route->handler],
                methods: [$route->method],
            );

            $this->routeCollection->add('route_' . $i, $symfonyRoute);
        }

        $this->matcher = new UrlMatcher($this->routeCollection, $this->context);
    }

    public function dispatch(string $method, string $uri): string
    {
        $this->context->setMethod($method);

        $result = $this->matcher->match($uri);

        return $result['_handler'];
    }
}
