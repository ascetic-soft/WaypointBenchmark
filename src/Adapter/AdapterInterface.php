<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use WaypointBench\RouteSet\RouteDefinition;

interface AdapterInterface
{
    /**
     * Router name for reports.
     */
    public function getName(): string;

    /**
     * Initialize the router instance.
     */
    public function initialize(): void;

    /**
     * Register routes from an array of RouteDefinition objects.
     *
     * @param RouteDefinition[] $routes
     */
    public function registerRoutes(array $routes): void;

    /**
     * Dispatch a request and return the response body string.
     */
    public function dispatch(string $method, string $uri): string;
}
