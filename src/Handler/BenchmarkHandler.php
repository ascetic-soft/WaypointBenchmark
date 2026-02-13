<?php

declare(strict_types=1);

namespace WaypointBench\Handler;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handler for Waypoint's cached benchmark.
 *
 * Waypoint's cache requires [class, method] handlers (closures can't be cached).
 * This handler reads the '_bench_handler' attribute set on the route to return
 * the correct handler identifier string.
 */
final class BenchmarkHandler
{
    public function __construct(
        private readonly Psr17Factory $factory,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = $request->getAttribute('_bench_handler', 'cached_ok');

        $response = $this->factory->createResponse(200);
        $response->getBody()->write($handler);

        return $response;
    }
}
