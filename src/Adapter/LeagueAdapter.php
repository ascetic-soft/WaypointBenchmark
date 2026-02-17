<?php

declare(strict_types=1);

namespace WaypointBench\Adapter;

use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;
use AsceticSoft\Psr7\HttpFactory;
use AsceticSoft\Psr7\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WaypointBench\Support\SimpleContainer;

final class LeagueAdapter implements AdapterInterface
{
    private Router $router;
    private HttpFactory $psr17Factory;

    public function getName(): string
    {
        return 'League';
    }

    public function initialize(): void
    {
        $this->psr17Factory = new HttpFactory();
        $strategy = new ApplicationStrategy();
        $strategy->setContainer(new SimpleContainer());

        $this->router = new Router();
        $this->router->setStrategy($strategy);
    }

    public function registerRoutes(array $routes): void
    {
        $factory = $this->psr17Factory;

        foreach ($routes as $route) {
            $handler = $route->handler;

            $this->router->map(
                $route->method,
                $route->pattern,
                static function (ServerRequestInterface $request) use ($factory, $handler): ResponseInterface {
                    $response = $factory->createResponse(200);
                    $response->getBody()->write($handler);

                    return $response;
                },
            );
        }
    }

    public function dispatch(string $method, string $uri): string
    {
        $request = new ServerRequest($method, $uri);
        $response = $this->router->dispatch($request);

        return (string) $response->getBody();
    }
}
