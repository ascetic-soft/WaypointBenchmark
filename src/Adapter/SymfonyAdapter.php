<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class SymfonyAdapter implements AdapterInterface, CacheableAdapterInterface
{
    private RouteCollection $routeCollection;
    private UrlMatcherInterface $matcher;
    private RequestContext $context;

    private const string CACHE_FILE = 'symfony_compiled_routes.php';

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

    // --- CacheableAdapterInterface ---

    public function warmCache(array $routes, string $cacheDir): void
    {
        $this->initialize();
        $this->registerRoutes($routes);

        $dumper = new CompiledUrlMatcherDumper($this->routeCollection);
        $compiledRoutes = $dumper->dump();

        file_put_contents(
            $cacheDir . '/' . self::CACHE_FILE,
            $compiledRoutes,
        );
    }

    public function initializeFromCache(string $cacheDir): void
    {
        $this->context = new RequestContext();
        $compiledRoutes = require $cacheDir . '/' . self::CACHE_FILE;

        $this->matcher = new CompiledUrlMatcher($compiledRoutes, $this->context);
    }

    public function clearCache(string $cacheDir): void
    {
        $file = $cacheDir . '/' . self::CACHE_FILE;
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
