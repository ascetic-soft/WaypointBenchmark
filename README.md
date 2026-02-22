# Waypoint Router Benchmark

[Русская версия](README.ru.md)

Benchmark suite comparing [Waypoint](https://github.com/ascetic-soft/Waypoint) router against popular PHP routing libraries.

## Routers Tested

| Router | Package | Version | Description |
|--------|---------|---------|-------------|
| **Waypoint** | [ascetic-soft/waypoint](https://github.com/ascetic-soft/Waypoint) | v1.2.1 | PSR-15 router with prefix-trie matching |
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

> **Each benchmark measures the real request-response lifecycle with separate warmup and request timing.**
>
> Every "request" = full router bootstrap + dispatch:
> - **Cacheable routers** (FastRoute, Symfony, Waypoint): cache is pre-warmed once, then each request loads from cache + dispatches
> - **Non-cacheable routers**: each request initializes the router from scratch + registers all routes + dispatches
>
> Timing is split into two phases:
> - **Warmup** — first (cold) request after cache is built. May include opcache misses, autoloader overhead, etc.
> - **Request** — median of subsequent (warm) requests. Reflects steady-state production performance.
>
> All 9 routers compete in every scenario on equal terms. Routers that don't support caching pay the full initialization cost — that's their trade-off.

---

## Benchmark Results

> PHP 8.4.5 | Linux | 2026-02-22 | 20 runs per test (median)

### 1. Static Route Dispatching

100 static routes. Each request = boot router + dispatch one route.

**Dispatch first route (best case)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 0.005 | 0.001 | 100% |
| 2 | **Waypoint** | **1.662** | **0.002** | **200%** |
| 3 | Symfony | 0.045 | 0.004 | 300% |
| 4 | AltoRouter | 0.991 | 0.012 | 999% |
| 5 | Bramus | 1.345 | 0.026 | 2,199% |
| 6 | PHRoute | 3.138 | 0.059 | 4,997% |
| 7 | League | 10.582 | 0.161 | 13,592% |
| 8 | Nette | 7.577 | 0.246 | 20,688% |
| 9 | Laravel | 35.401 | 1.015 | 85,450% |

**Dispatch last route (worst case)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | **Waypoint** | **0.621** | **0.002** | **100%** |
| 2 | FastRoute | 0.005 | 0.002 | 100% |
| 3 | AltoRouter | 0.494 | 0.015 | 650% |
| 4 | Symfony | 0.658 | 0.017 | 700% |
| 5 | Bramus | 0.838 | 0.045 | 1,901% |
| 6 | PHRoute | 0.113 | 0.059 | 2,501% |
| 7 | League | 0.315 | 0.145 | 6,102% |
| 8 | Nette | 0.786 | 0.224 | 9,452% |
| 9 | Laravel | 3.309 | 0.956 | 40,260% |

**Dispatch all 100 routes (100 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 0.451 | 0.012 | 100% |
| 2 | **Waypoint** | **2.195** | **0.031** | **260%** |
| 3 | Symfony | 3.653 | 0.634 | 5,340% |
| 4 | AltoRouter | 1.070 | 0.875 | 7,370% |
| 5 | Bramus | 2.568 | 2.352 | 19,810% |
| 6 | PHRoute | 4.938 | 4.395 | 37,020% |
| 7 | League | 13.580 | 9.610 | 80,940% |
| 8 | Nette | 14.864 | 13.215 | 111,300% |
| 9 | Laravel | 72.225 | 30.104 | 253,551% |

### 2. Dynamic Route Dispatching

100 dynamic routes with `{id}` and `{slug}` parameters. Each request = boot router + dispatch one route.

**Dispatch first route (best case)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 0.085 | 0.001 | 100% |
| 2 | Symfony | 0.028 | 0.002 | 200% |
| 3 | **Waypoint** | **0.496** | **0.004** | **300%** |
| 4 | AltoRouter | 0.064 | 0.015 | 1,299% |
| 5 | Bramus | 0.053 | 0.018 | 1,499% |
| 6 | League | 0.705 | 0.191 | 16,091% |
| 7 | Nette | 0.516 | 0.198 | 16,690% |
| 8 | Laravel | 0.440 | 0.215 | 18,089% |
| 9 | PHRoute | 0.671 | 0.227 | 19,089% |

**Dispatch last route (worst case)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | **Waypoint** | **0.526** | **0.002** | **100%** |
| 2 | FastRoute | 0.332 | 0.002 | 100% |
| 3 | Symfony | 0.038 | 0.009 | 400% |
| 4 | Bramus | 0.437 | 0.037 | 1,550% |
| 5 | AltoRouter | 0.569 | 0.083 | 3,499% |
| 6 | League | 0.278 | 0.198 | 8,349% |
| 7 | PHRoute | 0.609 | 0.227 | 9,548% |
| 8 | Nette | 1.577 | 0.261 | 10,998% |
| 9 | Laravel | 1.080 | 0.534 | 22,496% |

**Dispatch all 100 routes (100 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 0.147 | 0.090 | 100% |
| 2 | **Waypoint** | **1.015** | **0.093** | **103%** |
| 3 | Symfony | 0.943 | 0.484 | 537% |
| 4 | Bramus | 2.821 | 2.624 | 2,908% |
| 5 | AltoRouter | 5.200 | 4.721 | 5,232% |
| 6 | League | 19.201 | 18.586 | 20,597% |
| 7 | PHRoute | 22.549 | 21.864 | 24,230% |
| 8 | Nette | 22.777 | 22.590 | 25,034% |
| 9 | Laravel | 43.229 | 40.738 | 45,146% |

### 3. High-Load and Large-Scale

Each "request" = boot router from scratch or from cache + dispatch. The router initializes on every single request.

**500 mixed routes — dispatch all (500 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | **Waypoint** | **3.504** | **0.322** | **100%** |
| 2 | FastRoute | 1.149 | 0.437 | 136% |
| 3 | Symfony | 11.341 | 10.023 | 3,115% |
| 4 | AltoRouter | 50.694 | 49.252 | 15,307% |
| 5 | Bramus | 76.639 | 73.770 | 22,927% |
| 6 | PHRoute | 346.687 | 347.982 | 108,150% |
| 7 | League | 376.037 | 374.844 | 116,498% |
| 8 | Nette | 490.674 | 483.586 | 150,294% |
| 9 | Laravel | 790.868 | 791.921 | 246,123% |

**100 dynamic routes x50 repeated dispatch (5,000 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 4.661 | 4.460 | 100% |
| 2 | **Waypoint** | **6.106** | **5.008** | **112%** |
| 3 | Symfony | 23.963 | 24.498 | 549% |
| 4 | Bramus | 135.165 | 131.460 | 2,948% |
| 5 | AltoRouter | 232.293 | 236.271 | 5,298% |
| 6 | League | 962.688 | 955.455 | 21,425% |
| 7 | PHRoute | 1,096.143 | 1,092.873 | 24,507% |
| 8 | Nette | 1,147.740 | 1,135.098 | 25,453% |
| 9 | Laravel | 2,198.798 | 2,190.101 | 49,111% |

**1,000 mixed routes — dispatch all (1,000 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | **Waypoint** | **5.889** | **0.683** | **100%** |
| 2 | FastRoute | 2.655 | 1.547 | 227% |
| 3 | Symfony | 40.953 | 39.172 | 5,738% |
| 4 | AltoRouter | 202.317 | 201.372 | 29,496% |
| 5 | Bramus | 275.514 | 268.851 | 39,381% |
| 6 | PHRoute | 1,379.466 | 1,377.291 | 201,742% |
| 7 | League | 1,492.840 | 1,492.054 | 218,552% |
| 8 | Nette | 1,906.317 | 1,905.095 | 279,053% |
| 9 | Laravel | 3,260.167 | 3,246.485 | 475,536% |

---

## Analysis and Conclusions

### Tier Classification

Based on steady-state request time (after warmup), where cacheable routers use their cache:

**Tier 1 — Fastest request handling** (compiled/cached dispatchers):
- **Waypoint** — prefix-trie matching with cache support. **1st place in 4 out of 9 scenarios**, including all "last route" dispatches and all large-scale tests (500+ routes). At 1,000 mixed routes, Waypoint delivers 0.683 ms — **2.3× faster than FastRoute**. In this benchmark, Waypoint uses its core trie matcher directly (without PSR-15 middleware pipeline), just like FastRoute and Symfony use their core matchers.
- **FastRoute** — fastest in 5 out of 9 scenarios (first-route dispatches and small-to-medium scale). `cachedDispatcher()` loads compiled regex data from a PHP file, delivering 0.001 ms per single dispatch at 100 static routes.

**Tier 2 — Cached mid-range** (strong single-dispatch, but falls behind at scale):
- **Symfony** — `CompiledUrlMatcher` performs well for single dispatches (2nd in dynamic first-route at 0.002 ms), but drops to distant 3rd in all multi-dispatch scenarios. At 1,000 mixed routes, 57× slower than Waypoint (39.172 ms vs 0.683 ms).

**Tier 3 — Lightweight routers** (fast single dispatch, O(n) scaling):
- **AltoRouter** — fast at small scale, but linear O(n) matching causes dramatic slowdown at scale. At 1,000 mixed routes: 201.372 ms (295× Waypoint).
- **Bramus** — similar pattern to AltoRouter. Fast single-route dispatch but O(n) scaling costs it dearly: 268.851 ms at 1,000 mixed routes (394× Waypoint).

**Tier 4 — Non-cached mid-range**:
- **PHRoute** — built on FastRoute's core but no caching, so it pays full `simpleDispatcher()` cost per request.
- **Nette** — no caching, O(n) matching.

**Tier 5 — Heavy bootstrap**:
- **League** — PSR-15, no caching.
- **Laravel** — heaviest bootstrap. Carries full framework routing infrastructure (Container, Dispatcher, Events).

### Key Findings

#### 1. Waypoint takes 1st place in 4 out of 9 scenarios

Waypoint's cached prefix-trie delivers sub-millisecond dispatching across all scenarios — **1st place in 4 scenarios, 2nd in 4, and 3rd in 1** (dynamic first-route, where Symfony takes 2nd):

| Scenario | FastRoute | **Waypoint** | 3rd place | Waypoint vs FastRoute |
|----------|-----------|-------------|-----------|----------------------|
| 100 static, first | 0.001 ms | **0.002 ms** | Symfony 0.004 ms | 2.0× |
| **100 static, last** | 0.002 ms | **0.002 ms (1st)** | AltoRouter 0.015 ms | **1.0× (tie)** |
| 100 dynamic, first | 0.001 ms | **0.004 ms (3rd)** | Symfony 0.002 ms | 4.0× |
| **100 dynamic, last** | 0.002 ms | **0.002 ms (1st)** | Symfony 0.009 ms | **1.0× (tie)** |
| 100 static, all | 0.012 ms | **0.031 ms** | Symfony 0.634 ms | 2.6× |
| 100 dynamic, all | 0.090 ms | **0.093 ms** | Symfony 0.484 ms | 1.03× |
| **500 mixed, all** | 0.437 ms | **0.322 ms (1st)** | Symfony 10.023 ms | **0.74× (Waypoint wins!)** |
| 100x50 repeated | 4.460 ms | **5.008 ms** | Symfony 24.498 ms | 1.12× |
| **1000 mixed, all** | 1.547 ms | **0.683 ms (1st)** | Symfony 39.172 ms | **0.44× (Waypoint wins!)** |

The gap between Waypoint and FastRoute narrows as route count grows, and **Waypoint overtakes FastRoute starting at 500 routes**. At 1,000 mixed routes, Waypoint is 2.3× faster.

#### 2. At 500+ routes, Waypoint overtakes FastRoute

The crossover point where Waypoint's prefix-trie becomes faster than FastRoute's compiled regex is at ~500 routes:

- **500 mixed routes**: Waypoint 0.322 ms (1st) vs FastRoute 0.437 ms (2nd, 36% slower)
- **1,000 mixed routes**: Waypoint 0.683 ms (1st) vs FastRoute 1.547 ms (2nd, 127% slower)

This proves that the prefix-trie data structure scales dramatically better than compiled regex at large route counts. FastRoute's regex groups grow linearly with route count, while Waypoint's trie provides near-constant-time lookups.

#### 3. Warmup reveals caching architecture

The warmup/request split clearly shows which routers benefit from caching and how their cache loading works:

| Router | Static first warmup | Static first request | Warmup/Request ratio |
|--------|-------------------|---------------------|---------------------|
| **Waypoint** | 1.662 ms | 0.002 ms | **831×** |
| FastRoute | 0.005 ms | 0.001 ms | 5× |
| Symfony | 0.045 ms | 0.004 ms | 11× |
| AltoRouter | 0.991 ms | 0.012 ms | 83× |
| Laravel | 35.401 ms | 1.015 ms | 35× |

Waypoint shows the largest warmup-to-request ratio (831×), meaning its first request carries significant cache-loading overhead, but subsequent requests are extremely fast. FastRoute's warmup is nearly identical to request time because its compiled cache is a simple PHP array `require`. For non-cacheable routers, warmup and request times are nearly identical — they pay the same initialization cost every time.

#### 4. Waypoint dramatically outperforms Symfony at scale

Symfony's `CompiledUrlMatcher` is competitive for single dispatches (2nd in dynamic first-route at 0.002 ms). But in multi-dispatch scenarios, Waypoint is an order of magnitude faster:

| Scenario | Waypoint | Symfony | Waypoint advantage |
|----------|----------|---------|-------------------|
| 100 static, all | 0.031 ms | 0.634 ms | **20× faster** |
| 100 dynamic, all | 0.093 ms | 0.484 ms | **5.2× faster** |
| 500 mixed, all | 0.322 ms | 10.023 ms | **31× faster** |
| 100x50 repeated | 5.008 ms | 24.498 ms | **4.9× faster** |
| 1000 mixed, all | 0.683 ms | 39.172 ms | **57× faster** |

At 1,000 routes, Waypoint is **57× faster** than Symfony. This demonstrates that Waypoint's cached prefix-trie is fundamentally more efficient for repeated dispatches than Symfony's compiled URL matcher.

#### 5. Waypoint crushes all lightweight routers at scale

The prefix-trie data structure gives Waypoint a massive structural advantage over O(n) matchers (AltoRouter, Bramus). While lightweight routers are competitive in single-dispatch tests, they cannot keep up with Waypoint at scale:

| Scenario | Waypoint | AltoRouter | Bramus |
|----------|----------|------------|--------|
| 100 static, all | **0.031 ms (2nd)** | 0.875 ms (4th) | 2.352 ms (5th) |
| 100 dynamic, all | **0.093 ms (2nd)** | 4.721 ms (5th) | 2.624 ms (4th) |
| 500 mixed, all | **0.322 ms (1st!)** | 49.252 ms (4th) | 73.770 ms (5th) |
| 100x50 repeated | **5.008 ms (2nd)** | 236.271 ms (5th) | 131.460 ms (4th) |
| 1000 mixed, all | **0.683 ms (1st!)** | 201.372 ms (4th) | 268.851 ms (5th) |

At 1,000 routes, Waypoint is **295× faster** than AltoRouter and **394× faster** than Bramus. The O(n) matchers' dispatch time scales linearly with route count, while Waypoint's trie-based cache provides near-constant-time lookups.

#### 6. Waypoint vs League: trie matcher vs PSR-7 pipeline

League is the only router in this benchmark that creates real PSR-7 Request/Response objects on every dispatch. Waypoint, like FastRoute and Symfony, uses its core matcher directly and returns raw strings. This architectural difference contributes to the gap:

| Scenario | Waypoint | League | Gap |
|----------|----------|--------|-----|
| 100 static, first | 0.002 ms | 0.161 ms | 81× faster |
| 100 dynamic, all | 0.093 ms | 18.586 ms | 200× faster |
| 500 mixed, all | 0.322 ms | 374.844 ms | 1,164× faster |
| 1000 mixed, all | 0.683 ms | 1,492.054 ms | **2,184× faster** |

The gap is partly explained by League's PSR-7 overhead (creating `ServerRequest`/`Response` on every dispatch) and partly by the lack of caching — League reinitializes and re-registers all routes on every request.

#### 7. Dynamic last-route dispatch: Waypoint ties with FastRoute

In dynamic last-route dispatch (the worst-case scenario for route matching), Waypoint takes **1st place** at 0.002 ms — tying with FastRoute (0.002 ms) and dramatically beating Symfony (0.009 ms), Bramus (0.037 ms), and AltoRouter (0.083 ms). This confirms that the prefix-trie provides consistently fast lookups regardless of route position, unlike O(n) matchers where last-route performance degrades.

#### 8. Laravel is consistently last

Laravel's full-framework routing infrastructure makes it the heaviest router. A single request with 100 static routes takes 1.015 ms (request time) — 1,015× slower than FastRoute. At 1,000 routes, a full dispatch cycle takes 3,246 ms vs Waypoint's 0.683 ms. This is expected — Laravel's router is designed for a full-framework context where the bootstrap cost is amortized across the request lifecycle.

### Recommendations

| Use case | Recommended router |
|----------|--------------------|
| Maximum raw performance at ≤100 routes | **FastRoute** (with `cachedDispatcher`) |
| Best performance at scale (500+ routes) | **Waypoint** (with cache) — beats FastRoute starting at 500 routes |
| Near-FastRoute performance with trie matching | **Waypoint** (with cache) — within 1–4× at ≤100 routes, faster at 500+ |
| Framework with compiled matching | **Symfony Routing** (with `CompiledUrlMatcher`) |
| Small app, few routes (<50), no caching needed | **AltoRouter** or **Bramus** |
| PSR-15 stack with full pipeline | **League Route** |
| FastRoute core without caching | **PHRoute** |

### About this benchmark

- Every request simulates a real PHP-FPM lifecycle: the router initializes from scratch or loads from cache, then dispatches
- Cacheable routers (FastRoute, Symfony, Waypoint) have their cache pre-warmed before the timing loop — just like a production deployment
- A **warmup request** runs first to separate cold-cache overhead (opcache, autoloader, etc.) from steady-state performance
- **Warmup (ms)** = first request time (cold); **Request (ms)** = median of subsequent runs (warm)
- Non-cacheable routers pay the full `initialize() + registerRoutes()` cost on every request — that's the real cost of not having cache support
- All routers handle identical route sets generated deterministically by `RouteGenerator`
- Results are from **20 runs (median)** on 2026-02-22, PHP 8.4.5, Linux
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
