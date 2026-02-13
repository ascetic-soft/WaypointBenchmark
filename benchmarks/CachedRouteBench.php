<?php

declare(strict_types=1);

namespace WaypointBench\Benchmarks;

use PhpBench\Attributes as Bench;
use WaypointBench\Adapter\CacheableAdapterInterface;
use WaypointBench\Adapter\FastRouteAdapter;
use WaypointBench\Adapter\SymfonyAdapter;
use WaypointBench\Adapter\WaypointAdapter;
use WaypointBench\RouteSet\RouteGenerator;

/**
 * Benchmark: cached route loading and dispatching.
 *
 * Only routers with built-in caching support are tested:
 * Waypoint (compileTo/loadCache), FastRoute (cachedDispatcher), Symfony (CompiledUrlMatcher).
 */
#[Bench\Groups(['cached'])]
#[Bench\BeforeMethods(['setUpCacheDir'])]
#[Bench\AfterMethods(['tearDownCacheDir'])]
class CachedRouteBench
{
    private string $cacheDir;

    public function setUpCacheDir(): void
    {
        $this->cacheDir = dirname(__DIR__) . '/var/cache';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function tearDownCacheDir(): void
    {
        if (is_dir($this->cacheDir)) {
            $files = glob($this->cacheDir . '/*');
            if ($files !== false) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }
            rmdir($this->cacheDir);
        }
    }

    /**
     * @return iterable<string, array{adapter: CacheableAdapterInterface}>
     */
    public function provideCacheableAdapters(): iterable
    {
        yield 'Waypoint' => ['adapter' => new WaypointAdapter()];
        yield 'FastRoute' => ['adapter' => new FastRouteAdapter()];
        yield 'Symfony' => ['adapter' => new SymfonyAdapter()];
    }

    /**
     * Cached: Load 100 static routes from cache, dispatch all.
     */
    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideCacheableAdapters')]
    public function benchCached100StaticDispatchAll(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::staticRoutes(100);

        $adapter->warmCache($routes, $this->cacheDir);
        $adapter->initializeFromCache($this->cacheDir);

        foreach ($routes as $route) {
            $adapter->dispatch($route->method, $route->testUri);
        }

        $adapter->clearCache($this->cacheDir);
    }

    /**
     * Cached: Load 100 dynamic routes from cache, dispatch all.
     */
    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideCacheableAdapters')]
    public function benchCached100DynamicDispatchAll(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::dynamicRoutes(100);

        $adapter->warmCache($routes, $this->cacheDir);
        $adapter->initializeFromCache($this->cacheDir);

        foreach ($routes as $route) {
            $adapter->dispatch($route->method, $route->testUri);
        }

        $adapter->clearCache($this->cacheDir);
    }

    /**
     * Cached: Load 500 mixed routes from cache, dispatch all.
     */
    #[Bench\Revs(20)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideCacheableAdapters')]
    public function benchCached500MixedDispatchAll(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::mixedRoutes(500);

        $adapter->warmCache($routes, $this->cacheDir);
        $adapter->initializeFromCache($this->cacheDir);

        foreach ($routes as $route) {
            $adapter->dispatch($route->method, $route->testUri);
        }

        $adapter->clearCache($this->cacheDir);
    }

    /**
     * Cached: Load 1000 mixed routes from cache, dispatch all.
     */
    #[Bench\Revs(10)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideCacheableAdapters')]
    public function benchCached1000MixedDispatchAll(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::mixedRoutes(1000);

        $adapter->warmCache($routes, $this->cacheDir);
        $adapter->initializeFromCache($this->cacheDir);

        foreach ($routes as $route) {
            $adapter->dispatch($route->method, $route->testUri);
        }

        $adapter->clearCache($this->cacheDir);
    }
}
