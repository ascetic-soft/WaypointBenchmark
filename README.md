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
| 1 | FastRoute | 0.017 | 100% |
| 2 | Symfony | 0.090 | 543% |
| 3 | AltoRouter | 0.887 | 5,336% |
| 4 | Bramus | 1.405 | 8,450% |
| 5 | **Waypoint** | **1.847** | **11,114%** |
| 6 | PHRoute | 4.625 | 27,820% |
| 7 | Nette | 8.983 | 54,041% |
| 8 | League | 13.584 | 81,718% |
| 9 | Laravel | 40.617 | 244,341% |

**Dispatch last route (worst case)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 0.014 | 100% |
| 2 | AltoRouter | 0.822 | 5,767% |
| 3 | Symfony | 0.867 | 6,083% |
| 4 | **Waypoint** | **0.914** | **6,417%** |
| 5 | Bramus | 1.629 | 11,433% |
| 6 | PHRoute | 2.611 | 18,325% |
| 7 | Nette | 5.114 | 35,891% |
| 8 | League | 6.182 | 43,391% |
| 9 | Laravel | 21.952 | 154,072% |

**Dispatch all 100 routes (100 requests)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 0.560 | 100% |
| 2 | **Waypoint** | **2.860** | **510%** |
| 3 | Symfony | 38.305 | 6,835% |
| 4 | AltoRouter | 49.076 | 8,757% |
| 5 | Bramus | 73.076 | 13,040% |
| 6 | PHRoute | 219.846 | 39,229% |
| 7 | Nette | 314.912 | 56,193% |
| 8 | League | 520.646 | 92,904% |
| 9 | Laravel | 1,435.471 | 256,146% |

### 2. Dynamic Route Dispatching

100 dynamic routes with `{id}` and `{slug}` parameters. Each request = boot router + dispatch one route.

**Dispatch first route (best case)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | Symfony | 0.077 | 100% |
| 2 | FastRoute | 0.095 | 123% |
| 3 | AltoRouter | 0.361 | 468% |
| 4 | Bramus | 0.514 | 666% |
| 5 | **Waypoint** | **0.682** | **883%** |
| 6 | Nette | 4.281 | 5,548% |
| 7 | PHRoute | 6.149 | 7,968% |
| 8 | League | 7.384 | 9,568% |
| 9 | Laravel | 10.954 | 14,194% |

**Dispatch last route (worst case)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 0.386 | 100% |
| 2 | **Waypoint** | **0.628** | **163%** |
| 3 | Symfony | 0.762 | 198% |
| 4 | Bramus | 1.458 | 378% |
| 5 | AltoRouter | 1.926 | 499% |
| 6 | Nette | 6.372 | 1,651% |
| 7 | PHRoute | 6.705 | 1,738% |
| 8 | League | 7.794 | 2,020% |
| 9 | Laravel | 21.981 | 5,696% |

**Dispatch all 100 routes (100 requests)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 1.678 | 100% |
| 2 | **Waypoint** | **4.365** | **260%** |
| 3 | Symfony | 38.268 | 2,281% |
| 4 | Bramus | 75.663 | 4,510% |
| 5 | AltoRouter | 85.444 | 5,093% |
| 6 | Nette | 490.442 | 29,233% |
| 7 | PHRoute | 605.919 | 36,117% |
| 8 | League | 741.033 | 44,170% |
| 9 | Laravel | 1,595.953 | 95,129% |

### 3. High-Load and Large-Scale

Each "request" = boot router from scratch or from cache + dispatch. The router initializes on every single request.

**500 mixed routes — dispatch all (500 requests)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 8.838 | 100% |
| 2 | **Waypoint** | **17.129** | **194%** |
| 3 | Symfony | 843.766 | 9,547% |
| 4 | AltoRouter | 1,433.117 | 16,215% |
| 5 | Bramus | 1,780.217 | 20,142% |
| 6 | Nette | 10,009.903 | 113,255% |
| 7 | PHRoute | 10,262.511 | 116,113% |
| 8 | League | 15,432.601 | 174,609% |
| 9 | Laravel | 35,289.225 | 399,274% |

**100 dynamic routes x50 repeated dispatch (5,000 requests)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 80.392 | 100% |
| 2 | **Waypoint** | **151.700** | **189%** |
| 3 | Symfony | 1,851.921 | 2,304% |
| 4 | Bramus | 3,701.786 | 4,605% |
| 5 | AltoRouter | 4,315.213 | 5,368% |
| 6 | Nette | 24,566.455 | 30,558% |
| 7 | PHRoute | 30,447.787 | 37,874% |
| 8 | League | 36,814.857 | 45,794% |
| 9 | Laravel | 80,220.752 | 99,787% |

**1,000 mixed routes — dispatch all (1,000 requests)**

| Rank | Router | Time (ms) | vs Fastest |
|------|--------|-----------|------------|
| 1 | FastRoute | 27.018 | 100% |
| 2 | **Waypoint** | **34.351** | **127%** |
| 3 | Symfony | 3,324.234 | 12,304% |
| 4 | AltoRouter | 5,567.821 | 20,607% |
| 5 | Bramus | 6,921.092 | 25,616% |
| 6 | Nette | 39,983.556 | 147,986% |
| 7 | PHRoute | 41,047.135 | 151,923% |
| 8 | League | 61,716.880 | 228,425% |
| 9 | Laravel | 140,164.887 | 518,775% |

---

## Analysis and Conclusions

### Tier Classification

Based on full request lifecycle (boot + dispatch), where cacheable routers use their cache:

**Tier 1 — Fastest request handling** (compiled cache pays off):
- **FastRoute** — overall fastest in almost every scenario. `cachedDispatcher()` loads compiled regex data from a PHP file, making cold and cached performance nearly identical. 0.017 ms per request at 100 static routes.
- **Waypoint** — prefix-trie matching with cache support. **Solidly 2nd place in all "dispatch all" and high-load scenarios.** At 1,000 mixed routes, only 27% slower than FastRoute (34.4 ms vs 27.0 ms). Proves that a cached prefix-trie can compete directly with compiled regex at scale. In this benchmark, Waypoint uses its core trie matcher directly (without PSR-15 middleware pipeline), just like FastRoute and Symfony use their core matchers.

**Tier 2 — Cached mid-range** (strong single-dispatch, but falls behind at scale):
- **Symfony** — `CompiledUrlMatcher` performs well for single dispatches (1st in dynamic first-route at 0.077 ms), but drops to 3rd in all multi-dispatch scenarios. At 1,000 mixed routes, 123× slower than FastRoute (3,324 ms vs 27 ms). Wins 1st place in dynamic first-route dispatch, beating FastRoute (0.077 ms vs 0.095 ms).

**Tier 3 — Lightweight routers** (fast single dispatch, O(n) scaling):
- **AltoRouter** — fast at small scale (2nd in static last-route), but linear O(n) matching causes dramatic slowdown at scale. At 1,000 mixed routes: 5,568 ms (206× FastRoute).
- **Bramus** — similar pattern to AltoRouter. Fast single-route dispatch but O(n) scaling costs it dearly: 6,921 ms at 1,000 mixed routes (256× FastRoute).

**Tier 4 — Non-cached mid-range**:
- **PHRoute** — built on FastRoute's core but no caching, so it pays full `simpleDispatcher()` cost per request. 1,738–151,923% of FastRoute depending on scenario.
- **Nette** — no caching, O(n) matching. 1,651–147,986% of FastRoute at scale.

**Tier 5 — Heavy bootstrap**:
- **League** — PSR-15, no caching. 2,020–228,425% of FastRoute at high load.
- **Laravel** — heaviest bootstrap. 5,696–518,775% of FastRoute. Carries full framework routing infrastructure (Container, Dispatcher, Events).

### Key Findings

#### 1. FastRoute dominates single-dispatch scenarios

FastRoute with `cachedDispatcher` wins most single-dispatch tests. Its cache loading is nearly instant — the compiled regex data is a simple PHP array `require`. This gives it sub-millisecond performance for single requests.

| Scenario | FastRoute | 2nd place | Gap |
|----------|-----------|-----------|-----|
| 100 static, first | 0.017 ms | Symfony 0.090 ms | 5.3× |
| 100 static, last | 0.014 ms | AltoRouter 0.822 ms | 58.7× |
| 100 dynamic, first | 0.095 ms | Symfony 0.077 ms | Symfony wins |
| 100 dynamic, last | 0.386 ms | Waypoint 0.628 ms | 1.6× |

#### 2. Waypoint takes 2nd place in every multi-dispatch scenario

In all "dispatch all" and high-load scenarios, Waypoint is **consistently 2nd**, directly behind FastRoute — and far ahead of every other router, including Symfony:

| Scenario | FastRoute | **Waypoint** | 3rd place | Waypoint vs FastRoute |
|----------|-----------|-------------|-----------|----------------------|
| 100 static, all | 0.560 ms | **2.860 ms** | Symfony 38.3 ms | 5.1× |
| 100 dynamic, all | 1.678 ms | **4.365 ms** | Symfony 38.3 ms | 2.6× |
| 500 mixed, all | 8.838 ms | **17.129 ms** | Symfony 843.8 ms | 1.9× |
| 100x50 repeated | 80.392 ms | **151.700 ms** | Symfony 1,851.9 ms | 1.9× |
| 1000 mixed, all | 27.018 ms | **34.351 ms** | Symfony 3,324.2 ms | **1.3×** |

At 1,000 mixed routes, Waypoint is only **27% slower** than FastRoute. The gap narrows as route count grows: from 5.1× at 100 static routes down to just 1.3× at 1,000 mixed routes.

#### 3. Waypoint leapfrogs Symfony at scale

In single-dispatch tests, Symfony's `CompiledUrlMatcher` competes well (winning 1st place in dynamic first-route dispatch at 0.077 ms). But in multi-dispatch scenarios, Waypoint dramatically outperforms Symfony:

| Scenario | Waypoint | Symfony | Waypoint advantage |
|----------|----------|---------|-------------------|
| 100 static, all | 2.860 ms | 38.305 ms | **13.4× faster** |
| 100 dynamic, all | 4.365 ms | 38.268 ms | **8.8× faster** |
| 500 mixed, all | 17.129 ms | 843.766 ms | **49.3× faster** |
| 100x50 repeated | 151.700 ms | 1,851.921 ms | **12.2× faster** |
| 1000 mixed, all | 34.351 ms | 3,324.234 ms | **96.8× faster** |

At 1,000 routes, Waypoint is nearly **97× faster** than Symfony. This demonstrates that Waypoint's cached prefix-trie is fundamentally more efficient for repeated dispatches than Symfony's compiled URL matcher.

#### 4. Waypoint crushes all lightweight routers at scale

The prefix-trie data structure gives Waypoint a massive structural advantage over O(n) matchers (AltoRouter, Bramus). While lightweight routers are competitive in single-dispatch tests, they cannot keep up with Waypoint at scale:

| Scenario | Waypoint | AltoRouter | Bramus |
|----------|----------|------------|--------|
| 100 static, all | **2.860 ms (2nd)** | 49.076 ms (4th) | 73.076 ms (5th) |
| 100 dynamic, all | **4.365 ms (2nd)** | 85.444 ms (5th) | 75.663 ms (4th) |
| 500 mixed, all | **17.129 ms (2nd)** | 1,433.117 ms (4th) | 1,780.217 ms (5th) |
| 100x50 repeated | **151.700 ms (2nd)** | 4,315.213 ms (5th) | 3,701.786 ms (4th) |
| 1000 mixed, all | **34.351 ms (2nd)** | 5,567.821 ms (4th) | 6,921.092 ms (5th) |

At 1,000 routes, Waypoint is **162× faster** than AltoRouter and **201× faster** than Bramus. The O(n) matchers' dispatch time scales linearly with route count, while Waypoint's trie-based cache provides near-constant-time lookups.

#### 5. Waypoint vs League: trie matcher vs PSR-7 pipeline

League is the only router in this benchmark that creates real PSR-7 Request/Response objects on every dispatch. Waypoint, like FastRoute and Symfony, uses its core matcher directly and returns raw strings. This architectural difference contributes to the gap:

| Scenario | Waypoint | League | Gap |
|----------|----------|--------|-----|
| 100 static, first | 1.847 ms | 13.584 ms | 7.4× faster |
| 100 dynamic, all | 4.365 ms | 741.033 ms | 169.8× faster |
| 500 mixed, all | 17.129 ms | 15,432.601 ms | 901.2× faster |
| 1000 mixed, all | 34.351 ms | 61,716.880 ms | **1,796.7× faster** |

The gap is partly explained by League's PSR-7 overhead (creating `ServerRequest`/`Response` on every dispatch) and partly by the lack of caching — League reinitializes and re-registers all routes on every request.

#### 6. Dynamic last-route dispatch: Waypoint takes 2nd place

In dynamic last-route dispatch (the worst-case scenario for route matching), Waypoint takes **2nd place** at 0.628 ms — beating Symfony (0.762 ms), Bramus (1.458 ms), and AltoRouter (1.926 ms). This confirms that the prefix-trie provides consistently fast lookups regardless of route position, unlike O(n) matchers where last-route performance degrades.

#### 7. Laravel is consistently last

Laravel's full-framework routing infrastructure makes it the heaviest router. A single request with 100 static routes takes 40.6 ms — 2,389× slower than FastRoute. At 1,000 routes, a full dispatch cycle takes 140 seconds vs FastRoute's 0.027 seconds. This is expected — Laravel's router is designed for a full-framework context where the bootstrap cost is amortized across the request lifecycle.

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
