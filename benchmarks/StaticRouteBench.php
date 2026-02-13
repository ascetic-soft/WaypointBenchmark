<?php

declare(strict_types=1);

namespace WaypointBench\Benchmarks;

use PhpBench\Attributes as Bench;
use WaypointBench\Adapter\AdapterInterface;
use WaypointBench\RouteSet\RouteGenerator;

/**
 * Benchmark: dispatching static routes (no parameters).
 */
#[Bench\Groups(['static'])]
class StaticRouteBench extends AbstractRouteBench
{
    /**
     * Dispatch the first static route out of 100 registered.
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    #[Bench\ParamProviders('provideAdapters')]
    public function benchFirstStaticRoute(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::staticRoutes(100);

        $adapter->initialize();
        $adapter->registerRoutes($routes);

        $first = $routes[0];
        $adapter->dispatch($first->method, $first->testUri);
    }

    /**
     * Dispatch the last static route out of 100 registered (worst case).
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    #[Bench\ParamProviders('provideAdapters')]
    public function benchLastStaticRoute(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::staticRoutes(100);

        $adapter->initialize();
        $adapter->registerRoutes($routes);

        $last = $routes[array_key_last($routes)];
        $adapter->dispatch($last->method, $last->testUri);
    }

    /**
     * Dispatch all 100 static routes sequentially.
     */
    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideAdapters')]
    public function benchAllStaticRoutes(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::staticRoutes(100);

        $adapter->initialize();
        $adapter->registerRoutes($routes);

        foreach ($routes as $route) {
            $adapter->dispatch($route->method, $route->testUri);
        }
    }
}
