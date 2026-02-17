<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use FastRoute;
use WaypointBench\RouteSet\RouteDefinition;

final class FastRouteAdapter implements AdapterInterface, CacheableAdapterInterface
{
    private FastRoute\Dispatcher $dispatcher;

    private const string CACHE_FILE = 'fastroute_cache.php';

    public function getName(): string
    {
        return 'FastRoute';
    }

    public function initialize(): void
    {
        // Nothing to initialize; dispatcher is created in registerRoutes
    }

    public function registerRoutes(array $routes): void
    {
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

    // --- CacheableAdapterInterface ---

    public function warmCache(array $routes, string $cacheDir): void
    {
        $cacheFile = $cacheDir . '/' . self::CACHE_FILE;

        // Remove existing cache so cachedDispatcher re-generates it
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }

        $this->dispatcher = FastRoute\cachedDispatcher(
            function (FastRoute\RouteCollector $r) use ($routes): void {
                foreach ($routes as $route) {
                    $r->addRoute($route->method, $route->pattern, $route->handler);
                }
            },
            ['cacheFile' => $cacheFile],
        );
    }

    public function initializeFromCache(string $cacheDir): void
    {
        if (isset($this->dispatcher)) {
            return;
        }

        $cacheFile = $cacheDir . '/' . self::CACHE_FILE;

        // Load from existing cache — the callback is a no-op since cache exists
        $this->dispatcher = FastRoute\cachedDispatcher(
            static function (FastRoute\RouteCollector $r): void {
                // Not called when cache file exists
            },
            ['cacheFile' => $cacheFile],
        );
    }

    public function clearCache(string $cacheDir): void
    {
        unset($this->dispatcher);

        $file = $cacheDir . '/' . self::CACHE_FILE;
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
