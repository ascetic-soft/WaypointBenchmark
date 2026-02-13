<?php

declare(strict_types=1);

namespace WaypointBench\RouteSet;

/**
 * Immutable value object representing a single route definition for benchmarks.
 */
final readonly class RouteDefinition
{
    /**
     * @param string   $method   HTTP method (GET, POST, etc.)
     * @param string   $pattern  Route pattern (e.g. /users/{id})
     * @param string   $handler  Handler identifier string (returned on dispatch)
     * @param string   $testUri  Concrete URI to test dispatch against this route
     */
    public function __construct(
        public string $method,
        public string $pattern,
        public string $handler,
        public string $testUri,
    ) {}
}
