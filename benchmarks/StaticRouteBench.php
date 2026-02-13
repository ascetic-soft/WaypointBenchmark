<?php

declare(strict_types=1);

namespace WaypointBench\Benchmarks;

use PhpBench\Attributes as Bench;

/**
 * Benchmark: real request lifecycle with static routes (no parameters).
 *
 * Each rev simulates a real HTTP request: boot router (from cache or fresh) + dispatch.
 */
#[Bench\Groups(['static'])]
class StaticRouteBench extends AbstractRouteBench
{
    /**
     * Single request: dispatch the first static route out of 100.
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    #[Bench\ParamProviders('provideAdapters')]
    #[Bench\BeforeMethods(['setupStaticRoutes100'])]
    #[Bench\AfterMethods(['clearCache'])]
    public function benchFirstStaticRoute(array $params): void
    {
        $adapter = $params['adapter'];

        $this->bootAdapter($adapter);

        $first = $this->routes[0];
        $adapter->dispatch($first->method, $first->testUri);
    }

    /**
     * Single request: dispatch the last static route out of 100 (worst case).
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(2)]
    #[Bench\ParamProviders('provideAdapters')]
    #[Bench\BeforeMethods(['setupStaticRoutes100'])]
    #[Bench\AfterMethods(['clearCache'])]
    public function benchLastStaticRoute(array $params): void
    {
        $adapter = $params['adapter'];

        $this->bootAdapter($adapter);

        $last = $this->routes[array_key_last($this->routes)];
        $adapter->dispatch($last->method, $last->testUri);
    }

    /**
     * 100 requests: dispatch each of 100 static routes (boot per request).
     */
    #[Bench\Revs(50)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideAdapters')]
    #[Bench\BeforeMethods(['setupStaticRoutes100'])]
    #[Bench\AfterMethods(['clearCache'])]
    public function benchAllStaticRoutes(array $params): void
    {
        $adapter = $params['adapter'];

        foreach ($this->routes as $route) {
            $this->bootAdapter($adapter);
            $adapter->dispatch($route->method, $route->testUri);
        }
    }
}
