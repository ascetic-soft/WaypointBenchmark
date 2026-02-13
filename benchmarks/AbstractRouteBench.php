<?php

declare(strict_types=1);

namespace WaypointBench\Benchmarks;

use WaypointBench\Adapter\AdapterInterface;
use WaypointBench\Adapter\AltoRouterAdapter;
use WaypointBench\Adapter\BramusAdapter;
use WaypointBench\Adapter\FastRouteAdapter;
use WaypointBench\Adapter\LaravelAdapter;
use WaypointBench\Adapter\LeagueAdapter;
use WaypointBench\Adapter\NetteAdapter;
use WaypointBench\Adapter\PHRouteAdapter;
use WaypointBench\Adapter\SymfonyAdapter;
use WaypointBench\Adapter\WaypointAdapter;
use WaypointBench\RouteSet\RouteDefinition;

/**
 * Base class for all router benchmarks.
 *
 * Provides adapter creation and route set generation via param providers.
 */
abstract class AbstractRouteBench
{
    protected AdapterInterface $adapter;

    /** @var RouteDefinition[] */
    protected array $routes = [];

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

    protected function setupAdapter(AdapterInterface $adapter, array $routes): void
    {
        $this->adapter = $adapter;
        $this->routes = $routes;
        $this->adapter->initialize();
        $this->adapter->registerRoutes($routes);
    }
}
