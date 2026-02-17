# Waypoint Router Benchmark

Benchmark suite comparing [Waypoint](https://github.com/ascetic-soft/Waypoint) router against popular PHP routing libraries.

## Routers Tested

| Router | Package | Version | Description |
|--------|---------|---------|-------------|
| **Waypoint** | [ascetic-soft/waypoint](https://github.com/ascetic-soft/Waypoint) | v1.1.1 | PSR-15 router with prefix-trie matching |
| **FastRoute** | [nikic/fast-route](https://github.com/nikic/FastRoute) | v1.3.0 | Popular standalone regex-based router |
| **Symfony** | [symfony/routing](https://symfony.com/doc/current/routing.html) | v7.4.4 | Symfony framework routing component |
| **Laravel** | [illuminate/routing](https://github.com/illuminate/routing) | v11.47.0 | Laravel framework router |
| **League** | [league/route](https://github.com/thephpleague/route) | v6.2.0 | PSR-15 compatible router |
| **Nette** | [nette/routing](https://github.com/nette/routing) | v3.1.2 | Nette framework router |
| **Bramus** | [bramus/router](https://github.com/bramus/router) | v1.6.1 | Lightweight regex router |
| **AltoRouter** | [altorouter/altorouter](https://github.com/dannyvankooten/AltoRouter) | v2.0.3 | Simple lightweight router |
| **PHRoute** | [phroute/phroute](https://github.com/mrjgreen/phroute) | v2.2.0 | FastRoute-based router |

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

Available scenarios: `static`, `dynamic`, `highload`

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
vendor/bin/phpbench run --report=aggregate --group=highload
```

### Makefile shortcuts

```bash
make install      # composer install
make bench        # Run CLI benchmark
make phpbench     # Run PHPBench
make all          # Install + benchmark
```

---

## Benchmark Methodology

> **Each benchmark measures the real request-response lifecycle.**
>
> Every "request" = full router bootstrap + dispatch:
> - **Cacheable routers** (FastRoute, Symfony, Waypoint): cache is pre-warmed once, then each request loads from cache + dispatches
> - **Non-cacheable routers**: each request initializes the router from scratch + registers all routes + dispatches
>
> All 9 routers compete in every scenario on equal terms. Routers that don't support caching pay the full initialization cost — that's their trade-off.

---

## Benchmark Results

> PHP 8.4.5 | Linux | 2026-02-17 | 1 run per test

### 1. Static Route Dispatching

100 static routes. Each request = boot router + dispatch one route.

**Dispatch first route (best case)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 0.015 | 100% |
| 2 | Symfony | 0.089 | 577% |
| 3 | AltoRouter | 0.868 | 5,623% |
| 4 | Bramus | 1.418 | 9,185% |
| 5 | **Waypoint** | **1.726** | **11,185%** |
| 6 | PHRoute | 4.631 | 30,000% |
| 7 | Nette | 8.978 | 58,169% |
| 8 | League | 13.531 | 87,662% |
| 9 | Laravel | 40.256 | 260,808% |

**Dispatch last route (worst case)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 0.014 | 100% |
| 2 | **Waypoint** | **0.775** | **5,442%** |
| 3 | AltoRouter | 0.830 | 5,825% |
| 4 | Symfony | 0.870 | 6,108% |
| 5 | Bramus | 1.641 | 11,516% |
| 6 | PHRoute | 2.643 | 18,550% |
| 7 | Nette | 5.102 | 35,808% |
| 8 | League | 6.197 | 43,491% |
| 9 | Laravel | 21.750 | 152,655% |

**Dispatch all 100 routes (100 requests)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 0.647 | 100% |
| 2 | **Waypoint** | **3.392** | **524%** |
| 3 | Symfony | 43.686 | 6,751% |
| 4 | AltoRouter | 49.771 | 7,692% |
| 5 | Bramus | 73.462 | 11,353% |
| 6 | PHRoute | 220.430 | 34,065% |
| 7 | Nette | 315.656 | 48,781% |
| 8 | League | 517.895 | 80,035% |
| 9 | Laravel | 1,445.123 | 223,329% |

### 2. Dynamic Route Dispatching

100 dynamic routes with `{id}` and `{slug}` parameters. Each request = boot router + dispatch one route.

**Dispatch first route (best case)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | Symfony | 0.080 | 100% |
| 2 | FastRoute | 0.132 | 166% |
| 3 | AltoRouter | 0.368 | 463% |
| 4 | Bramus | 0.514 | 646% |
| 5 | **Waypoint** | **0.632** | **794%** |
| 6 | Nette | 4.341 | 5,457% |
| 7 | PHRoute | 6.181 | 7,770% |
| 8 | League | 7.353 | 9,243% |
| 9 | Laravel | 10.738 | 13,498% |

**Dispatch last route (worst case)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 0.459 | 100% |
| 2 | **Waypoint** | **0.493** | **107%** |
| 3 | Symfony | 0.752 | 164% |
| 4 | Bramus | 1.458 | 317% |
| 5 | AltoRouter | 1.910 | 416% |
| 6 | Nette | 6.465 | 1,407% |
| 7 | PHRoute | 6.647 | 1,447% |
| 8 | League | 7.570 | 1,648% |
| 9 | Laravel | 22.464 | 4,889% |

**Dispatch all 100 routes (100 requests)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 1.472 | 100% |
| 2 | **Waypoint** | **3.989** | **271%** |
| 3 | Symfony | 37.537 | 2,550% |
| 4 | Bramus | 74.165 | 5,037% |
| 5 | AltoRouter | 86.562 | 5,880% |
| 6 | Nette | 489.341 | 33,237% |
| 7 | PHRoute | 608.800 | 41,351% |
| 8 | League | 733.960 | 49,852% |
| 9 | Laravel | 1,635.078 | 111,059% |

### 3. High-Load and Large-Scale

Each "request" = boot router from scratch or from cache + dispatch. The router initializes on every single request.

**500 mixed routes — dispatch all (500 requests)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 8.826 | 100% |
| 2 | **Waypoint** | **16.813** | **190%** |
| 3 | Symfony | 844.556 | 9,568% |
| 4 | AltoRouter | 1,433.410 | 16,240% |
| 5 | Bramus | 1,796.941 | 20,359% |
| 6 | Nette | 10,049.220 | 113,853% |
| 7 | PHRoute | 10,291.982 | 116,604% |
| 8 | League | 15,408.548 | 174,572% |
| 9 | Laravel | 35,349.927 | 400,500% |

**100 dynamic routes x50 repeated dispatch (5,000 requests)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 80.728 | 100% |
| 2 | **Waypoint** | **147.307** | **182%** |
| 3 | Symfony | 1,855.242 | 2,298% |
| 4 | Bramus | 3,683.131 | 4,562% |
| 5 | AltoRouter | 4,249.115 | 5,264% |
| 6 | Nette | 23,996.369 | 29,725% |
| 7 | PHRoute | 30,020.653 | 37,188% |
| 8 | League | 36,949.284 | 45,770% |
| 9 | Laravel | 79,991.495 | 99,088% |

**1,000 mixed routes — dispatch all (1,000 requests)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 25.876 | 100% |
| 2 | **Waypoint** | **33.875** | **131%** |
| 3 | Symfony | 3,274.968 | 12,656% |
| 4 | AltoRouter | 5,649.211 | 21,832% |
| 5 | Bramus | 7,093.965 | 27,415% |
| 6 | Nette | 40,297.732 | 155,733% |
| 7 | PHRoute | 41,488.658 | 160,335% |
| 8 | League | 61,764.349 | 238,692% |
| 9 | Laravel | 140,604.369 | 543,373% |

---

## Analysis and Conclusions

### Tier Classification

Based on full request lifecycle (boot + dispatch), where cacheable routers use their cache:

**Tier 1 — Fastest request handling** (compiled cache pays off):
- **FastRoute** — overall fastest in almost every scenario. `cachedDispatcher()` loads compiled regex data from a PHP file, making cold and cached performance nearly identical. 0.015 ms per request at 100 static routes.
- **Waypoint** — prefix-trie matching with cache support. **Solidly 2nd place in all "dispatch all" and high-load scenarios.** At 1,000 mixed routes, only 31% slower than FastRoute (33.9 ms vs 25.9 ms). Proves that a cached prefix-trie can compete directly with compiled regex at scale. In this benchmark, Waypoint uses its core trie matcher directly (without PSR-15 middleware pipeline), just like FastRoute and Symfony use their core matchers.

**Tier 2 — Cached mid-range** (strong single-dispatch, but falls behind at scale):
- **Symfony** — `CompiledUrlMatcher` performs well for single dispatches (1st in dynamic first-route at 0.080 ms), but drops to 3rd in all multi-dispatch scenarios. At 1,000 mixed routes, 127× slower than FastRoute (3,275 ms vs 26 ms). Wins 1st place in dynamic first-route dispatch, beating FastRoute (0.080 ms vs 0.132 ms).

**Tier 3 — Lightweight routers** (fast single dispatch, O(n) scaling):
- **AltoRouter** — fast at small scale (3rd in static last-route), but linear O(n) matching causes dramatic slowdown at scale. At 1,000 mixed routes: 5,649 ms (218× FastRoute).
- **Bramus** — similar pattern to AltoRouter. Fast single-route dispatch but O(n) scaling costs it dearly: 7,094 ms at 1,000 mixed routes (274× FastRoute).

**Tier 4 — Non-cached mid-range**:
- **PHRoute** — built on FastRoute's core but no caching, so it pays full `simpleDispatcher()` cost per request. 1,447–160,335% of FastRoute depending on scenario.
- **Nette** — no caching, O(n) matching. 1,407–155,733% of FastRoute at scale.

**Tier 5 — Heavy bootstrap**:
- **League** — PSR-15, no caching. 1,648–238,692% of FastRoute at high load.
- **Laravel** — heaviest bootstrap. 4,889–543,373% of FastRoute. Carries full framework routing infrastructure (Container, Dispatcher, Events).

### Key Findings

#### 1. FastRoute dominates single-dispatch scenarios

FastRoute with `cachedDispatcher` wins most single-dispatch tests. Its cache loading is nearly instant — the compiled regex data is a simple PHP array `require`. This gives it sub-millisecond performance for single requests.

| Scenario | FastRoute | 2nd place | Gap |
|----------|-----------|-----------|-----|
| 100 static, first | 0.015 ms | Symfony 0.089 ms | 5.9× |
| 100 static, last | 0.014 ms | Waypoint 0.775 ms | 55.4× |
| 100 dynamic, first | 0.132 ms | Symfony 0.080 ms | Symfony wins |
| 100 dynamic, last | 0.459 ms | Waypoint 0.493 ms | 1.1× |

#### 2. Waypoint takes 2nd place in every multi-dispatch scenario

In all "dispatch all" and high-load scenarios, Waypoint is **consistently 2nd**, directly behind FastRoute — and far ahead of every other router, including Symfony:

| Scenario | FastRoute | **Waypoint** | 3rd place | Waypoint vs FastRoute |
|----------|-----------|-------------|-----------|----------------------|
| 100 static, all | 0.647 ms | **3.392 ms** | Symfony 43.7 ms | 5.2× |
| 100 dynamic, all | 1.472 ms | **3.989 ms** | Symfony 37.5 ms | 2.7× |
| 500 mixed, all | 8.826 ms | **16.813 ms** | Symfony 844.6 ms | 1.9× |
| 100x50 repeated | 80.728 ms | **147.307 ms** | Symfony 1,855.2 ms | 1.8× |
| 1000 mixed, all | 25.876 ms | **33.875 ms** | Symfony 3,275.0 ms | **1.3×** |

At 1,000 mixed routes, Waypoint is only **31% slower** than FastRoute. The gap narrows as route count grows: from 5.2× at 100 static routes down to just 1.3× at 1,000 mixed routes.

#### 3. Waypoint leapfrogs Symfony at scale

In single-dispatch tests, Symfony's `CompiledUrlMatcher` competes well (winning 1st place in dynamic first-route dispatch at 0.080 ms). But in multi-dispatch scenarios, Waypoint dramatically outperforms Symfony:

| Scenario | Waypoint | Symfony | Waypoint advantage |
|----------|----------|---------|-------------------|
| 100 static, all | 3.392 ms | 43.686 ms | **12.9× faster** |
| 100 dynamic, all | 3.989 ms | 37.537 ms | **9.4× faster** |
| 500 mixed, all | 16.813 ms | 844.556 ms | **50.2× faster** |
| 100x50 repeated | 147.307 ms | 1,855.242 ms | **12.6× faster** |
| 1000 mixed, all | 33.875 ms | 3,274.968 ms | **96.7× faster** |

At 1,000 routes, Waypoint is nearly **97× faster** than Symfony. This demonstrates that Waypoint's cached prefix-trie is fundamentally more efficient for repeated dispatches than Symfony's compiled URL matcher.

#### 4. Waypoint crushes all lightweight routers at scale

The prefix-trie data structure gives Waypoint a massive structural advantage over O(n) matchers (AltoRouter, Bramus). While lightweight routers are competitive in single-dispatch tests, they cannot keep up with Waypoint at scale:

| Scenario | Waypoint | AltoRouter | Bramus |
|----------|----------|------------|--------|
| 100 static, all | **3.392 ms (2nd)** | 49.771 ms (4th) | 73.462 ms (5th) |
| 100 dynamic, all | **3.989 ms (2nd)** | 86.562 ms (5th) | 74.165 ms (4th) |
| 500 mixed, all | **16.813 ms (2nd)** | 1,433.410 ms (4th) | 1,796.941 ms (5th) |
| 100x50 repeated | **147.307 ms (2nd)** | 4,249.115 ms (5th) | 3,683.131 ms (4th) |
| 1000 mixed, all | **33.875 ms (2nd)** | 5,649.211 ms (4th) | 7,093.965 ms (5th) |

At 1,000 routes, Waypoint is **167× faster** than AltoRouter and **209× faster** than Bramus. The O(n) matchers' dispatch time scales linearly with route count, while Waypoint's trie-based cache provides near-constant-time lookups.

#### 5. Waypoint vs League: trie matcher vs PSR-7 pipeline

League is the only router in this benchmark that creates real PSR-7 Request/Response objects on every dispatch. Waypoint, like FastRoute and Symfony, uses its core matcher directly and returns raw strings. This architectural difference contributes to the gap:

| Scenario | Waypoint | League | Gap |
|----------|----------|--------|-----|
| 100 static, first | 1.726 ms | 13.531 ms | 7.8× faster |
| 100 dynamic, all | 3.989 ms | 733.960 ms | 184.0× faster |
| 500 mixed, all | 16.813 ms | 15,408.548 ms | 916.5× faster |
| 1000 mixed, all | 33.875 ms | 61,764.349 ms | **1,823.2× faster** |

The gap is partly explained by League's PSR-7 overhead (creating `ServerRequest`/`Response` on every dispatch) and partly by the lack of caching — League reinitializes and re-registers all routes on every request.

#### 6. Dynamic last-route dispatch: Waypoint nearly matches FastRoute

In dynamic last-route dispatch (the worst-case scenario for route matching), Waypoint takes **2nd place** at 0.493 ms — only 7% behind FastRoute (0.459 ms) and beating Symfony (0.752 ms), Bramus (1.458 ms), and AltoRouter (1.910 ms). This confirms that the prefix-trie provides consistently fast lookups regardless of route position, unlike O(n) matchers where last-route performance degrades.

#### 7. Laravel is consistently last

Laravel's full-framework routing infrastructure makes it the heaviest router. A single request with 100 static routes takes 40.3 ms — 2,684× slower than FastRoute. At 1,000 routes, a full dispatch cycle takes 141 seconds vs FastRoute's 0.026 seconds. This is expected — Laravel's router is designed for a full-framework context where the bootstrap cost is amortized across the request lifecycle.

### Recommendations

| Use case | Recommended router |
|----------|--------------------|
| Maximum raw performance | **FastRoute** (with `cachedDispatcher`) |
| Near-FastRoute performance with trie matching | **Waypoint** (with cache) — only 1.3× slower at 1,000 routes |
| Framework with compiled matching | **Symfony Routing** (with `CompiledUrlMatcher`) |
| Small app, few routes (<50), no caching needed | **AltoRouter** or **Bramus** |
| PSR-15 stack with full pipeline | **League Route** |
| FastRoute core without caching | **PHRoute** |

### About this benchmark

- Every request simulates a real PHP-FPM lifecycle: the router initializes from scratch or loads from cache, then dispatches
- Cacheable routers (FastRoute, Symfony, Waypoint) have their cache pre-warmed before the timing loop — just like a production deployment
- Non-cacheable routers pay the full `initialize() + registerRoutes()` cost on every request — that's the real cost of not having cache support
- All routers handle identical route sets generated deterministically by `RouteGenerator`
- Results are from a **single run** (median) on 2026-02-17, PHP 8.4.5, Linux
- All routers use their core matching engines directly and return raw strings, except **League** which dispatches through a full PSR-7 pipeline (creating `ServerRequest`/`Response` on every request) — this reflects League's standard usage pattern

---

## Test Scenarios

### 1. Static Route Dispatching
100 static routes. Each "request" boots the router (from cache or fresh) and dispatches one route. Tests best-case, worst-case, and full-sweep dispatch.

### 2. Dynamic Route Dispatching
100 dynamic routes (with `{id}`, `{slug}` parameters). Same per-request lifecycle. Tests parameter extraction and regex matching under realistic conditions.

### 3. High-Load / Large-Scale
- 500 mixed routes: 500 requests (boot + dispatch each route)
- 100 dynamic routes x50 repeated: 5,000 requests
- 1,000 mixed routes: 1,000 requests (boot + dispatch each route)

---

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

The benchmark automatically detects cacheable adapters and uses the optimal path:
- **Cacheable**: `warmCache()` once → each request calls `initializeFromCache()` + `dispatch()`
- **Non-cacheable**: each request calls `initialize()` + `registerRoutes()` + `dispatch()`

Routes are generated deterministically by `RouteGenerator` to ensure fair comparison.

## Project Structure

```
├── bin/benchmark              # CLI benchmark runner
├── benchmarks/                # PHPBench benchmark classes
│   ├── AbstractRouteBench.php # Base class with cache management
│   ├── StaticRouteBench.php
│   ├── DynamicRouteBench.php
│   └── HighLoadBench.php
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
│   ├── Handler/
│   │   └── BenchmarkHandler.php
│   ├── RouteSet/
│   │   ├── RouteDefinition.php
│   │   └── RouteGenerator.php
│   └── Support/
│       └── SimpleContainer.php
├── var/cache/                 # Route cache files (gitignored)
├── composer.json
├── phpbench.json
└── Makefile
```

## License

MIT
