<?php

declare(strict_types=1);

namespace WaypointBench\Benchmarks;

use PhpBench\Attributes as Bench;

/**
 * Benchmark: high-load and large-scale scenarios with real request lifecycle.
 *
 * Each dispatch simulates a separate HTTP request: boot router + dispatch.
 * Cacheable routers benefit from loading pre-compiled data; others pay the full
 * initialization cost on every request.
 */
#[Bench\Groups(['highload'])]
class HighLoadBench extends AbstractRouteBench
{
    /**
     * 500 requests: register 500 mixed routes, dispatch each (boot per request).
     */
    #[Bench\Revs(5)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideAdapters')]
    #[Bench\BeforeMethods(['setupMixedRoutes500'])]
    #[Bench\AfterMethods(['clearCache'])]
    public function benchDispatchAll500Mixed(array $params): void
    {
        $adapter = $params['adapter'];

        foreach ($this->routes as $route) {
            $this->bootAdapter($adapter);
            $adapter->dispatch($route->method, $route->testUri);
        }
    }

    /**
     * 5000 requests: 100 dynamic routes dispatched 50 times each (boot per request).
     */
    #[Bench\Revs(2)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideAdapters')]
    #[Bench\BeforeMethods(['setupDynamicRoutes100'])]
    #[Bench\AfterMethods(['clearCache'])]
    public function benchRepeatedDispatch(array $params): void
    {
        $adapter = $params['adapter'];

        for ($i = 0; $i < 50; $i++) {
            foreach ($this->routes as $route) {
                $this->bootAdapter($adapter);
                $adapter->dispatch($route->method, $route->testUri);
            }
        }
    }

    /**
     * 1000 requests: register 1000 mixed routes, dispatch each (boot per request).
     */
    #[Bench\Revs(2)]
    #[Bench\Iterations(5)]
    #[Bench\Warmup(1)]
    #[Bench\ParamProviders('provideAdapters')]
    #[Bench\BeforeMethods(['setupMixedRoutes1000'])]
    #[Bench\AfterMethods(['clearCache'])]
    public function benchLargeScale1000(array $params): void
    {
        $adapter = $params['adapter'];

        foreach ($this->routes as $route) {
            $this->bootAdapter($adapter);
            $adapter->dispatch($route->method, $route->testUri);
        }
    }
}
