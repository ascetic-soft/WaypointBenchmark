<?php

declare(strict_types=1);

namespace WaypointBench\Benchmarks;

use WaypointBench\Adapter\AdapterInterface;
use WaypointBench\Adapter\AltoRouterAdapter;
use WaypointBench\Adapter\BramusAdapter;
use WaypointBench\Adapter\CacheableAdapterInterface;
use WaypointBench\Adapter\FastRouteAdapter;
use WaypointBench\Adapter\LaravelAdapter;
use WaypointBench\Adapter\LeagueAdapter;
use WaypointBench\Adapter\NetteAdapter;
use WaypointBench\Adapter\PHRouteAdapter;
use WaypointBench\Adapter\SymfonyAdapter;
use WaypointBench\Adapter\WaypointAdapter;
use WaypointBench\RouteSet\RouteDefinition;
use WaypointBench\RouteSet\RouteGenerator;

/**
 * Base class for all router benchmarks.
 *
 * Simulates real application request lifecycle:
 * - Cacheable adapters: warm cache once (BeforeMethods), then load from cache per request
 * - Non-cacheable adapters: initialize + register routes from scratch per request
 *
 * This mirrors production PHP behavior where each HTTP request bootstraps the router.
 */
abstract class AbstractRouteBench
{
    /** @var RouteDefinition[] */
    protected array $routes = [];

    private string $cacheDir;

    /**
     * @return iterable<string, array{adapter: AdapterInterface}>
     */
    public function provideAdapters(): iterable
    {
        yield 'Waypoint' => ['adapter' => new WaypointAdapter()];
        yield 'FastRoute' => ['adapter' => new FastRouteAdapter()];
        yield 'Symfony' => ['adapter' => new SymfonyAdapter()];
        yield 'Laravel' => ['adapter' => new LaravelAdapter()];
        yield 'League' => ['adapter' => new LeagueAdapter()];
        yield 'Nette' => ['adapter' => new NetteAdapter()];
        yield 'Bramus' => ['adapter' => new BramusAdapter()];
        yield 'AltoRouter' => ['adapter' => new AltoRouterAdapter()];
        yield 'PHRoute' => ['adapter' => new PHRouteAdapter()];
    }

    // ── Setup methods (called via BeforeMethods) ────────────────────────

    public function setupStaticRoutes100(array $params): void
    {
        $this->routes = RouteGenerator::staticRoutes(100);
        $this->warmCacheIfNeeded($params['adapter']);
    }

    public function setupDynamicRoutes100(array $params): void
    {
        $this->routes = RouteGenerator::dynamicRoutes(100);
        $this->warmCacheIfNeeded($params['adapter']);
    }

    public function setupMixedRoutes500(array $params): void
    {
        $this->routes = RouteGenerator::mixedRoutes(500);
        $this->warmCacheIfNeeded($params['adapter']);
    }

    public function setupMixedRoutes1000(array $params): void
    {
        $this->routes = RouteGenerator::mixedRoutes(1000);
        $this->warmCacheIfNeeded($params['adapter']);
    }

    // ── Teardown (called via AfterMethods) ──────────────────────────────

    public function clearCache(array $params): void
    {
        $adapter = $params['adapter'];
        if ($adapter instanceof CacheableAdapterInterface) {
            $adapter->clearCache($this->getCacheDir());
        }
    }

    // ── Request simulation ──────────────────────────────────────────────

    /**
     * Boot the adapter for a single request.
     *
     * Cacheable adapters load from pre-warmed cache (fast path).
     * Non-cacheable adapters initialize and register routes from scratch.
     */
    protected function bootAdapter(AdapterInterface $adapter): void
    {
        if ($adapter instanceof CacheableAdapterInterface) {
            $adapter->initializeFromCache($this->getCacheDir());
        } else {
            $adapter->initialize();
            $adapter->registerRoutes($this->routes);
        }
    }

    // ── Internal helpers ────────────────────────────────────────────────

    private function warmCacheIfNeeded(AdapterInterface $adapter): void
    {
        if ($adapter instanceof CacheableAdapterInterface) {
            $adapter->warmCache($this->routes, $this->getCacheDir());
        }
    }

    private function getCacheDir(): string
    {
        if (!isset($this->cacheDir)) {
            $this->cacheDir = \dirname(__DIR__) . '/var/cache';
            if (!is_dir($this->cacheDir) && !mkdir($this->cacheDir, 0777, true) && !is_dir($this->cacheDir)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $this->cacheDir));
            }
        }

        return $this->cacheDir;
    }
}
