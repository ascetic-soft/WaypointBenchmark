<?php

declare(strict_types=1);

namespace WaypointBench\Benchmarks;

use PhpBench\Attributes as Bench;
use WaypointBench\Adapter\AdapterInterface;
use WaypointBench\RouteSet\RouteGenerator;

/**
 * Benchmark: dispatching dynamic routes (with parameters).
 */
#[Bench\Groups(['dynamic'])]
class DynamicRouteBench extends AbstractRouteBench
{
    /**
     * Dispatch a single dynamic route out of 100 registered.
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    #[Bench\ParamProviders('provideAdapters')]
    public function benchSingleDynamicRoute(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::dynamicRoutes(100);

        $adapter->initialize();
        $adapter->registerRoutes($routes);

        $mid = $routes[50];
        $adapter->dispatch($mid->method, $mid->testUri);
    }

    /**
     * Dispatch the last dynamic route (worst case).
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    #[Bench\ParamProviders('provideAdapters')]
    public function benchLastDynamicRoute(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::dynamicRoutes(100);

        $adapter->initialize();
        $adapter->registerRoutes($routes);

        $last = $routes[array_key_last($routes)];
        $adapter->dispatch($last->method, $last->testUri);
    }

    /**
     * Dispatch all 100 dynamic routes sequentially.
     */
    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideAdapters')]
    public function benchAllDynamicRoutes(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::dynamicRoutes(100);

        $adapter->initialize();
        $adapter->registerRoutes($routes);

        foreach ($routes as $route) {
            $adapter->dispatch($route->method, $route->testUri);
        }
    }
}
