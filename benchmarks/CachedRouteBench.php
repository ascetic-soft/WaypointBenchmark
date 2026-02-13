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
 * Cache is warmed ONCE in BeforeMethods. The benchmark method only measures
 * initializeFromCache + dispatch (simulating production request).
 *
 * Only routers with built-in caching support are tested:
 * Waypoint (compileTo/loadCache), FastRoute (cachedDispatcher), Symfony (CompiledUrlMatcher).
 */
#[Bench\Groups(['cached'])]
class CachedRouteBench
{
    private string $cacheDir;

    private function ensureCacheDir(): void
    {
        $this->cacheDir = dirname(__DIR__) . '/var/cache';
        if (!is_dir($this->cacheDir)) {
            if (!mkdir($concurrentDirectory = $this->cacheDir, 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
    }

    // ── Warm methods (called once per iteration via BeforeMethods) ───────

    public function warmStatic100(array $params): void
    {
        $this->ensureCacheDir();
        $params['adapter']->warmCache(RouteGenerator::staticRoutes(100), $this->cacheDir);
    }

    public function warmDynamic100(array $params): void
    {
        $this->ensureCacheDir();
        $params['adapter']->warmCache(RouteGenerator::dynamicRoutes(100), $this->cacheDir);
    }

    public function warmMixed500(array $params): void
    {
        $this->ensureCacheDir();
        $params['adapter']->warmCache(RouteGenerator::mixedRoutes(500), $this->cacheDir);
    }

    public function warmMixed1000(array $params): void
    {
        $this->ensureCacheDir();
        $params['adapter']->warmCache(RouteGenerator::mixedRoutes(1000), $this->cacheDir);
    }

    // ── Cleanup (called after each iteration via AfterMethods) ──────────

    public function clearCache(array $params): void
    {
        $params['adapter']->clearCache($this->cacheDir);
    }

    // ── Param provider ──────────────────────────────────────────────────

    /**
     * @return iterable<string, array{adapter: CacheableAdapterInterface}>
     */
    public function provideCacheableAdapters(): iterable
    {
        yield 'Waypoint' => ['adapter' => new WaypointAdapter()];
        yield 'FastRoute' => ['adapter' => new FastRouteAdapter()];
        yield 'Symfony' => ['adapter' => new SymfonyAdapter()];
    }

    // ── Benchmarks (only load from cache + dispatch) ────────────────────

    /**
     * Cached: Load 100 static routes from cache, dispatch all.
     */
    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideCacheableAdapters')]
    #[Bench\BeforeMethods(['warmStatic100'])]
    #[Bench\AfterMethods(['clearCache'])]
    public function benchCached100StaticDispatchAll(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::staticRoutes(100);

        $adapter->initializeFromCache($this->cacheDir);

        foreach ($routes as $route) {
            $adapter->dispatch($route->method, $route->testUri);
        }
    }

    /**
     * Cached: Load 100 dynamic routes from cache, dispatch all.
     */
    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideCacheableAdapters')]
    #[Bench\BeforeMethods(['warmDynamic100'])]
    #[Bench\AfterMethods(['clearCache'])]
    public function benchCached100DynamicDispatchAll(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::dynamicRoutes(100);

        $adapter->initializeFromCache($this->cacheDir);

        foreach ($routes as $route) {
            $adapter->dispatch($route->method, $route->testUri);
        }
    }

    /**
     * Cached: Load 500 mixed routes from cache, dispatch all.
     */
    #[Bench\Revs(20)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideCacheableAdapters')]
    #[Bench\BeforeMethods(['warmMixed500'])]
    #[Bench\AfterMethods(['clearCache'])]
    public function benchCached500MixedDispatchAll(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::mixedRoutes(500);

        $adapter->initializeFromCache($this->cacheDir);

        foreach ($routes as $route) {
            $adapter->dispatch($route->method, $route->testUri);
        }
    }

    /**
     * Cached: Load 1000 mixed routes from cache, dispatch all.
     */
    #[Bench\Revs(10)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideCacheableAdapters')]
    #[Bench\BeforeMethods(['warmMixed1000'])]
    #[Bench\AfterMethods(['clearCache'])]
    public function benchCached1000MixedDispatchAll(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::mixedRoutes(1000);

        $adapter->initializeFromCache($this->cacheDir);

        foreach ($routes as $route) {
            $adapter->dispatch($route->method, $route->testUri);
        }
    }
}
