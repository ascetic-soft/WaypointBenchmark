<?php

declare(strict_types=1);

namespace WaypointBench\Benchmarks;

use PhpBench\Attributes as Bench;
use WaypointBench\Adapter\AdapterInterface;
use WaypointBench\RouteSet\RouteGenerator;

/**
 * Benchmark: high-load and large-scale scenarios.
 */
#[Bench\Groups(['highload'])]
class HighLoadBench extends AbstractRouteBench
{
    /**
     * Register 500 mixed routes, then dispatch all 500 routes.
     */
    #[Bench\Revs(20)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideAdapters')]
    public function benchDispatchAll500Mixed(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::mixedRoutes(500);

        $adapter->initialize();
        $adapter->registerRoutes($routes);

        foreach ($routes as $route) {
            $adapter->dispatch($route->method, $route->testUri);
        }
    }

    /**
     * Register 100 dynamic routes, then dispatch each one 50 times (5000 total).
     */
    #[Bench\Revs(5)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideAdapters')]
    public function benchRepeatedDispatch(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::dynamicRoutes(100);

        $adapter->initialize();
        $adapter->registerRoutes($routes);

        for ($i = 0; $i < 50; $i++) {
            foreach ($routes as $route) {
                $adapter->dispatch($route->method, $route->testUri);
            }
        }
    }

    /**
     * Register 1000 mixed routes, then dispatch all of them.
     */
    #[Bench\Revs(10)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideAdapters')]
    public function benchLargeScale1000(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::mixedRoutes(1000);

        $adapter->initialize();
        $adapter->registerRoutes($routes);

        foreach ($routes as $route) {
            $adapter->dispatch($route->method, $route->testUri);
        }
    }
}
