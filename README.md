# Waypoint Router Benchmark

Benchmark suite comparing [Waypoint](https://github.com/ascetic-soft/Waypoint) router against popular PHP routing libraries.

## Routers Tested

| Router | Package | Description |
|--------|---------|-------------|
| **Waypoint** | [ascetic-soft/waypoint](https://github.com/ascetic-soft/Waypoint) | PSR-15 router with prefix-trie matching |
| **FastRoute** | [nikic/fast-route](https://github.com/nikic/FastRoute) | Popular standalone regex-based router |
| **Symfony** | [symfony/routing](https://symfony.com/doc/current/routing.html) | Symfony framework routing component |
| **Laravel** | [illuminate/routing](https://github.com/illuminate/routing) | Laravel framework router |
| **League** | [league/route](https://github.com/thephpleague/route) | PSR-15 compatible router |
| **Nette** | [nette/routing](https://github.com/nette/routing) | Nette framework router |
| **AltoRouter** | [altorouter/altorouter](https://github.com/dannyvankooten/AltoRouter) | Lightweight router |
| **PHRoute** | [phroute/phroute](https://github.com/mrjgreen/phroute) | FastRoute-based router |

## Requirements

- PHP >= 8.4
- ext-mbstring
- Composer

## Installation

```bash
git clone https://github.com/ascetic-soft/Waypoint-benchmark.git
cd Waypoint-benchmark
composer install
```

## Usage

### CLI Benchmark (quick comparison)

Run all scenarios with all routers:

```bash
php bin/benchmark
```

Filter by router:

```bash
php bin/benchmark --router=waypoint,fastroute,symfony
```

Filter by scenario:

```bash
php bin/benchmark --scenario=static,dynamic
```

Available scenarios: `static`, `dynamic`, `registration`, `highload`, `cached`

Adjust number of runs (default 20):

```bash
php bin/benchmark --runs=50
```

### PHPBench (statistical benchmarks)

Run all benchmarks:

```bash
vendor/bin/phpbench run --report=aggregate
```

Filter by group:

```bash
vendor/bin/phpbench run --report=aggregate --group=static
vendor/bin/phpbench run --report=aggregate --group=dynamic
vendor/bin/phpbench run --report=aggregate --group=registration
vendor/bin/phpbench run --report=aggregate --group=highload
vendor/bin/phpbench run --report=aggregate --group=cached
```

### Makefile shortcuts

```bash
make install      # composer install
make bench        # Run CLI benchmark
make phpbench     # Run PHPBench
make all          # Install + benchmark
```

## Test Scenarios

### 1. Static Route Dispatching
Registers 100 static routes and dispatches first, last, and all routes.

### 2. Dynamic Route Dispatching
Registers 100 dynamic routes (with `{id}`, `{slug}` parameters) and dispatches first, last, and all routes.

### 3. Route Registration
Measures initialization + registration time for 100, 500, and 1000 routes.

### 4. High-Load / Large-Scale
- 500 mixed routes: register + dispatch all
- 100 dynamic routes x50 repeated dispatches
- 1000 mixed routes: register + dispatch all

### 5. Cached Route Dispatching
Tests routers with built-in caching support (Waypoint, FastRoute, Symfony).
Routes are compiled to cache files, then loaded and dispatched to measure production-like performance.
- 100 static routes: load from cache + dispatch all
- 100 dynamic routes: load from cache + dispatch all
- 500 mixed routes: load from cache + dispatch all
- 1000 mixed routes: load from cache + dispatch all

## Architecture

Each router is wrapped in an adapter implementing `AdapterInterface`:

```php
interface AdapterInterface
{
    public function getName(): string;
    public function initialize(): void;
    public function registerRoutes(array $routes): void;
    public function dispatch(string $method, string $uri): string;
}
```

Routers with caching support also implement `CacheableAdapterInterface`:

```php
interface CacheableAdapterInterface extends AdapterInterface
{
    public function warmCache(array $routes, string $cacheDir): void;
    public function initializeFromCache(string $cacheDir): void;
    public function clearCache(string $cacheDir): void;
}
```

Routes are generated deterministically by `RouteGenerator` to ensure fair comparison.

## Project Structure

```
├── bin/benchmark              # CLI benchmark runner
├── benchmarks/                # PHPBench benchmark classes
│   ├── AbstractRouteBench.php
│   ├── StaticRouteBench.php
│   ├── DynamicRouteBench.php
│   ├── RegistrationBench.php
│   ├── HighLoadBench.php
│   └── CachedRouteBench.php
├── src/
│   ├── Adapter/               # Router adapters
│   │   ├── AdapterInterface.php
│   │   ├── CacheableAdapterInterface.php
│   │   ├── WaypointAdapter.php
│   │   ├── FastRouteAdapter.php
│   │   ├── SymfonyAdapter.php
│   │   ├── LaravelAdapter.php
│   │   ├── LeagueAdapter.php
│   │   ├── NetteAdapter.php
│   │   ├── BramusAdapter.php
│   │   ├── AltoRouterAdapter.php
│   │   └── PHRouteAdapter.php
│   ├── Cli/
│   │   └── BenchmarkCommand.php
│   ├── RouteSet/
│   │   ├── RouteDefinition.php
│   │   └── RouteGenerator.php
│   ├── Handler/
│   │   └── BenchmarkHandler.php
│   └── Support/
│       └── SimpleContainer.php
├── composer.json
├── phpbench.json
└── Makefile
```

## License

MIT
