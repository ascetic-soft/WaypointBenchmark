<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use AsceticSoft\Waypoint\Router;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use WaypointBench\Handler\BenchmarkHandler;
use WaypointBench\Support\SimpleContainer;

final class WaypointAdapter implements AdapterInterface, CacheableAdapterInterface
{
    private Router $router;
    private Psr17Factory $psr17Factory;
    private SimpleContainer $container;

    private const string CACHE_FILE = 'waypoint_routes.php';

    public function getName(): string
    {
        return 'Waypoint';
    }

    public function initialize(): void
    {
        $this->container = new SimpleContainer();
        $this->psr17Factory = new Psr17Factory();
        $this->router = new Router($this->container);
    }

    public function registerRoutes(array $routes): void
    {
        $factory = $this->psr17Factory;

        foreach ($routes as $route) {
            $handler = $route->handler;
            $closure = static function () use ($factory, $handler) {
                $response = $factory->createResponse(200);
                $response->getBody()->write($handler);

                return $response;
            };

            $this->router->addRoute(
                path: $route->pattern,
                handler: $closure,
                methods: [$route->method],
            );
        }
    }

    public function dispatch(string $method, string $uri): string
    {
        $request = new ServerRequest($method, $uri);
        $response = $this->router->handle($request);

        return (string) $response->getBody();
    }

    // --- CacheableAdapterInterface ---

    public function warmCache(array $routes, string $cacheDir): void
    {
        $container = new SimpleContainer();
        $factory = new Psr17Factory();
        $container->set(BenchmarkHandler::class, new BenchmarkHandler($factory));

        $router = new Router($container);

        foreach ($routes as $route) {
            $router->addRoute(
                path: $route->pattern,
                handler: [BenchmarkHandler::class, 'handle'],
                methods: [$route->method],
            );
        }

        $router->compileTo($cacheDir . '/' . self::CACHE_FILE);
    }

    public function initializeFromCache(string $cacheDir): void
    {
        $this->container = new SimpleContainer();
        $this->psr17Factory = new Psr17Factory();
        $this->container->set(BenchmarkHandler::class, new BenchmarkHandler($this->psr17Factory));

        $this->router = new Router($this->container);
        $this->router->loadCache($cacheDir . '/' . self::CACHE_FILE);
    }

    public function clearCache(string $cacheDir): void
    {
        $file = $cacheDir . '/' . self::CACHE_FILE;
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
