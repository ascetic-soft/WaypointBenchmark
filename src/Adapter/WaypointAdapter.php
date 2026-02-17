<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use AsceticSoft\Waypoint\Cache\RouteCompiler;
use AsceticSoft\Waypoint\RouteRegistrar;
use AsceticSoft\Waypoint\TrieMatcher;
use AsceticSoft\Waypoint\UrlMatcherInterface;
use WaypointBench\Handler\BenchmarkHandler;

final class WaypointAdapter implements AdapterInterface, CacheableAdapterInterface
{
    private RouteRegistrar $registrar;
    private ?UrlMatcherInterface $matcher = null;

    private const string CACHE_FILE = 'waypoint_routes.php';

    public function getName(): string
    {
        return 'Waypoint';
    }

    public function initialize(): void
    {
        $this->registrar = new RouteRegistrar();
        $this->matcher = null;
    }

    public function registerRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            $this->registrar->addRoute(
                path: $route->pattern,
                handler: [BenchmarkHandler::class, 'handle'],
                methods: [$route->method],
            );
        }

        $this->matcher = new TrieMatcher($this->registrar->getRouteCollection());
    }

    public function dispatch(string $method, string $uri): string
    {
        $result = $this->matcher->match($method, $uri);
        $handler = $result->route->getHandler();

        return \is_array($handler) ? $handler[1] : 'closure';
    }

    // --- CacheableAdapterInterface ---

    public function warmCache(array $routes, string $cacheDir): void
    {
        $registrar = new RouteRegistrar();

        foreach ($routes as $route) {
            $registrar->addRoute(
                path: $route->pattern,
                handler: [BenchmarkHandler::class, 'handle'],
                methods: [$route->method],
            );
        }

        $compiler = new RouteCompiler();
        $compiler->compile(
            $registrar->getRouteCollection(),
            $cacheDir . '/' . self::CACHE_FILE,
        );
    }

    public function initializeFromCache(string $cacheDir): void
    {
        if ($this->matcher !== null) {
            return;
        }

        $compiler = new RouteCompiler();
        $this->matcher = $compiler->load($cacheDir . '/' . self::CACHE_FILE);
    }

    public function clearCache(string $cacheDir): void
    {
        $this->matcher = null;

        $file = $cacheDir . '/' . self::CACHE_FILE;
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
