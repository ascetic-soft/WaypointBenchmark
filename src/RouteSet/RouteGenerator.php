<?php

declare(strict_types=1);

namespace WaypointBench\RouteSet;

/**
 * Generates sets of route definitions for benchmark scenarios.
 *
 * All generated routes are guaranteed to have unique patterns.
 */
final class RouteGenerator
{
    private const array SEGMENTS = [
        'users', 'posts', 'comments', 'articles', 'categories',
        'tags', 'products', 'orders', 'invoices', 'settings',
        'dashboard', 'profile', 'notifications', 'messages', 'files',
        'reports', 'analytics', 'search', 'admin', 'api',
    ];

    private const array ACTIONS = [
        'list', 'show', 'create', 'update', 'delete',
        'export', 'import', 'archive', 'restore', 'sync',
    ];

    /**
     * Generate N static routes (no parameters).
     *
     * @return RouteDefinition[]
     */
    public static function staticRoutes(int $count): array
    {
        $routes = [];
        $segments = self::SEGMENTS;
        $actions = self::ACTIONS;
        $segCount = count($segments);
        $actCount = count($actions);

        for ($i = 0; $i < $count; $i++) {
            // Build unique path: /{segment}/{action}/{index}
            // Using multiple levels for realistic depth
            $seg1 = $segments[$i % $segCount];
            $seg2 = $segments[intdiv($i, $segCount) % $segCount];
            $action = $actions[$i % $actCount];

            $path = "/{$seg1}/{$seg2}/{$action}/r{$i}";
            $handler = "handler_static_{$i}";

            $routes[] = new RouteDefinition(
                method: 'GET',
                pattern: $path,
                handler: $handler,
                testUri: $path,
            );
        }

        return $routes;
    }

    /**
     * Generate N dynamic routes (with parameters).
     *
     * @return RouteDefinition[]
     */
    public static function dynamicRoutes(int $count): array
    {
        $routes = [];
        $segments = self::SEGMENTS;
        $segCount = count($segments);

        for ($i = 0; $i < $count; $i++) {
            $seg = $segments[$i % $segCount];

            // Alternate between 1 and 2 dynamic segments
            if ($i % 2 === 0) {
                $path = "/{$seg}/r{$i}/{id}";
                $testUri = "/{$seg}/r{$i}/42";
            } else {
                $path = "/{$seg}/r{$i}/{id}/{slug}";
                $testUri = "/{$seg}/r{$i}/42/hello-world";
            }

            $handler = "handler_dynamic_{$i}";

            $routes[] = new RouteDefinition(
                method: 'GET',
                pattern: $path,
                handler: $handler,
                testUri: $testUri,
            );
        }

        return $routes;
    }

    /**
     * Generate a mixed set of static and dynamic routes.
     *
     * @return RouteDefinition[]
     */
    public static function mixedRoutes(int $count): array
    {
        $staticCount = intdiv($count, 2);
        $dynamicCount = $count - $staticCount;

        return array_merge(
            self::staticRoutes($staticCount),
            self::dynamicRoutes($dynamicCount),
        );
    }
}
