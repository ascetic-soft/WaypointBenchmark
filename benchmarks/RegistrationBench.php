<?php

declare(strict_types=1);

namespace WaypointBench\Benchmarks;

use PhpBench\Attributes as Bench;
use WaypointBench\Adapter\AdapterInterface;
use WaypointBench\RouteSet\RouteGenerator;

/**
 * Benchmark: route initialization and registration performance.
 */
#[Bench\Groups(['registration'])]
class RegistrationBench extends AbstractRouteBench
{
    /**
     * Initialize router and register 100 static routes.
     */
    #[Bench\Revs(500)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    #[Bench\ParamProviders('provideAdapters')]
    public function benchRegister100Static(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::staticRoutes(100);

        $adapter->initialize();
        $adapter->registerRoutes($routes);
    }

    /**
     * Initialize router and register 500 mixed routes.
     */
    #[Bench\Revs(100)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideAdapters')]
    public function benchRegister500Mixed(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::mixedRoutes(500);

        $adapter->initialize();
        $adapter->registerRoutes($routes);
    }

    /**
     * Initialize router and register 1000 mixed routes.
     */
    #[Bench\Revs(50)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideAdapters')]
    public function benchRegister1000Mixed(array $params): void
    {
        $adapter = $params['adapter'];
        $routes = RouteGenerator::mixedRoutes(1000);

        $adapter->initialize();
        $adapter->registerRoutes($routes);
    }
}
