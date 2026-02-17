<?php

declare(strict_types=1);

namespace WaypointBench\Handler;

/**
 * Handler for Waypoint's cached benchmark.
 *
 * Waypoint's cache requires [class, method] handlers (closures can't be cached).
 * Returns a fixed string identifying a successful cached dispatch.
 */
final class BenchmarkHandler
{
    public function handle(): string
    {
        return 'cached_ok';
    }
}
