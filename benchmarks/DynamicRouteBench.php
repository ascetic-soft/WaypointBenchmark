<?php

declare(strict_types=1);

namespace WaypointBench\Benchmarks;

use PhpBench\Attributes as Bench;

/**
 * Benchmark: real request lifecycle with dynamic routes (with parameters).
 *
 * Each rev simulates a real HTTP request: boot router (from cache or fresh) + dispatch.
 */
#[Bench\Groups(['dynamic'])]
class DynamicRouteBench extends AbstractRouteBench
{
    /**
     * Single request: dispatch a middle dynamic route out of 100.
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    #[Bench\ParamProviders('provideAdapters')]
    #[Bench\BeforeMethods(['setupDynamicRoutes100'])]
    #[Bench\AfterMethods(['clearCache'])]
    public function benchSingleDynamicRoute(array $params): void
    {
        $adapter = $params['adapter'];

        $this->bootAdapter($adapter);

        $mid = $this->routes[50];
        $adapter->dispatch($mid->method, $mid->testUri);
    }

    /**
     * Single request: dispatch the last dynamic route (worst case).
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    #[Bench\ParamProviders('provideAdapters')]
    #[Bench\BeforeMethods(['setupDynamicRoutes100'])]
    #[Bench\AfterMethods(['clearCache'])]
    public function benchLastDynamicRoute(array $params): void
    {
        $adapter = $params['adapter'];

        $this->bootAdapter($adapter);

        $last = $this->routes[array_key_last($this->routes)];
        $adapter->dispatch($last->method, $last->testUri);
    }

    /**
     * 100 requests: dispatch each of 100 dynamic routes (boot per request).
     */
    #[Bench\Revs(50)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideAdapters')]
    #[Bench\BeforeMethods(['setupDynamicRoutes100'])]
    #[Bench\AfterMethods(['clearCache'])]
    public function benchAllDynamicRoutes(array $params): void
    {
        $adapter = $params['adapter'];

        foreach ($this->routes as $route) {
            $this->bootAdapter($adapter);
            $adapter->dispatch($route->method, $route->testUri);
        }
    }
}
