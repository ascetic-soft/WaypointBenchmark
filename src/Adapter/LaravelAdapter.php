<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\CallableDispatcher;
use Illuminate\Routing\Contracts\CallableDispatcher as CallableDispatcherContract;
use Illuminate\Routing\Router;
use WaypointBench\RouteSet\RouteDefinition;

final class LaravelAdapter implements AdapterInterface
{
    private Router $router;
    private Container $container;

    public function getName(): string
    {
        return 'Laravel';
    }

    public function initialize(): void
    {
        $this->container = new Container();

        // Bind the CallableDispatcher that Laravel's router needs internally
        $this->container->singleton(
            CallableDispatcherContract::class,
            static fn ($app) => new CallableDispatcher($app),
        );

        $events = new Dispatcher($this->container);
        $this->router = new Router($events, $this->container);
    }

    public function registerRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            $handler = $route->handler;

            $this->router->addRoute(
                $route->method,
                $route->pattern,
                static fn () => $handler,
            );
        }
    }

    public function dispatch(string $method, string $uri): string
    {
        $request = Request::create($uri, $method);

        $response = $this->router->dispatch($request);

        return $response->getContent();
    }
}
