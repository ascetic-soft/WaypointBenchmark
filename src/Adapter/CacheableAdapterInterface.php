<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use WaypointBench\RouteSet\RouteDefinition;

/**
 * Interface for router adapters that support route caching.
 *
 * Cache flow:
 *   1. warmCache() — register routes and compile to cache file (called once, not timed)
 *   2. initializeFromCache() — load from cache (timed, replaces initialize + registerRoutes)
 *   3. dispatch() — as usual
 */
interface CacheableAdapterInterface extends AdapterInterface
{
    /**
     * Register routes and compile/save cache to the given directory.
     *
     * @param RouteDefinition[] $routes
     */
    public function warmCache(array $routes, string $cacheDir): void;

    /**
     * Initialize the router from cached data.
     * Replaces the normal initialize() + registerRoutes() flow.
     */
    public function initializeFromCache(string $cacheDir): void;

    /**
     * Clean up any cache files.
     */
    public function clearCache(string $cacheDir): void;
}
