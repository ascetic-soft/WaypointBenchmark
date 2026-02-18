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

> PHP 8.4.5 | Linux | 2026-02-18 | 1 run per test

### 1. Static Route Dispatching

100 static routes. Each request = boot router + dispatch one route.

**Dispatch first route (best case)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 0.015 | 0.012 | 100% |
| 2 | **Waypoint** | **1.872** | **0.021** | **180%** |
| 3 | Symfony | 0.093 | 0.063 | 530% |
| 4 | AltoRouter | 0.899 | 0.354 | 2,980% |
| 5 | Bramus | 1.445 | 0.558 | 4,700% |
| 6 | PHRoute | 4.644 | 2.605 | 21,940% |
| 7 | Nette | 9.035 | 2.874 | 24,210% |
| 8 | League | 13.673 | 6.117 | 51,520% |
| 9 | Laravel | 41.002 | 12.095 | 101,870% |

**Dispatch last route (worst case)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 0.014 | 0.011 | 100% |
| 2 | **Waypoint** | **0.734** | **0.023** | **211%** |
| 3 | AltoRouter | 0.822 | 0.810 | 7,578% |
| 4 | Symfony | 0.882 | 0.819 | 7,667% |
| 5 | Bramus | 1.666 | 1.184 | 11,079% |
| 6 | PHRoute | 2.587 | 2.573 | 24,079% |
| 7 | Nette | 5.140 | 4.590 | 42,959% |
| 8 | League | 5.980 | 6.033 | 56,459% |
| 9 | Laravel | 22.648 | 21.073 | 197,225% |

**Dispatch all 100 routes (100 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 0.630 | 0.615 | 100% |
| 2 | **Waypoint** | **3.282** | **1.548** | **252%** |
| 3 | Symfony | 43.289 | 38.916 | 6,328% |
| 4 | AltoRouter | 49.739 | 49.633 | 8,070% |
| 5 | Bramus | 72.878 | 73.711 | 11,985% |
| 6 | PHRoute | 218.800 | 218.118 | 35,465% |
| 7 | Nette | 314.354 | 315.604 | 51,316% |
| 8 | League | 518.441 | 518.435 | 84,295% |
| 9 | Laravel | 1,428.354 | 1,427.355 | 232,082% |

### 2. Dynamic Route Dispatching

100 dynamic routes with `{id}` and `{slug}` parameters. Each request = boot router + dispatch one route.

**Dispatch first route (best case)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 0.121 | 0.017 | 100% |
| 2 | **Waypoint** | **0.519** | **0.033** | **200%** |
| 3 | Symfony | 0.088 | 0.056 | 336% |
| 4 | AltoRouter | 0.372 | 0.324 | 1,950% |
| 5 | Bramus | 0.520 | 0.486 | 2,921% |
| 6 | Nette | 4.323 | 4.204 | 25,292% |
| 7 | PHRoute | 6.365 | 6.164 | 37,084% |
| 8 | League | 8.090 | 7.468 | 44,927% |
| 9 | Laravel | 11.535 | 10.781 | 64,854% |

**Dispatch last route (worst case)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 0.393 | 0.028 | 100% |
| 2 | **Waypoint** | **0.515** | **0.036** | **125%** |
| 3 | Symfony | 0.758 | 0.710 | 2,492% |
| 4 | Bramus | 1.465 | 0.996 | 3,496% |
| 5 | AltoRouter | 1.897 | 1.380 | 4,842% |
| 6 | Nette | 6.154 | 5.716 | 20,059% |
| 7 | PHRoute | 6.581 | 6.176 | 21,675% |
| 8 | League | 7.599 | 7.442 | 26,117% |
| 9 | Laravel | 21.772 | 21.070 | 73,942% |

**Dispatch all 100 routes (100 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 1.690 | 1.612 | 100% |
| 2 | **Waypoint** | **4.075** | **2.763** | **171%** |
| 3 | Symfony | 37.963 | 37.716 | 2,339% |
| 4 | Bramus | 74.001 | 74.103 | 4,596% |
| 5 | AltoRouter | 86.097 | 85.310 | 5,291% |
| 6 | Nette | 490.104 | 489.578 | 30,364% |
| 7 | PHRoute | 607.953 | 608.228 | 37,723% |
| 8 | League | 738.509 | 737.192 | 45,721% |
| 9 | Laravel | 1,589.188 | 1,591.451 | 98,703% |

### 3. High-Load and Large-Scale

Each "request" = boot router from scratch or from cache + dispatch. The router initializes on every single request.

**500 mixed routes — dispatch all (500 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 9.059 | 8.201 | 100% |
| 2 | **Waypoint** | **16.138** | **10.410** | **127%** |
| 3 | Symfony | 847.165 | 841.313 | 10,259% |
| 4 | AltoRouter | 1,434.311 | 1,434.145 | 17,488% |
| 5 | Bramus | 1,781.164 | 1,784.132 | 21,756% |
| 6 | Nette | 10,051.102 | 10,066.637 | 122,753% |
| 7 | PHRoute | 10,315.358 | 10,337.738 | 126,059% |
| 8 | League | 15,411.551 | 15,403.482 | 187,831% |
| 9 | Laravel | 35,362.701 | 35,198.778 | 429,216% |

**100 dynamic routes x50 repeated dispatch (5,000 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 80.650 | 80.884 | 100% |
| 2 | **Waypoint** | **140.678** | **137.371** | **170%** |
| 3 | Symfony | 1,852.551 | 1,850.490 | 2,288% |
| 4 | Bramus | 3,698.855 | 3,699.565 | 4,574% |
| 5 | AltoRouter | 4,290.549 | 4,299.406 | 5,316% |
| 6 | Nette | 24,570.417 | 24,565.852 | 30,372% |
| 7 | PHRoute | 30,396.820 | 30,284.400 | 37,442% |
| 8 | League | 36,961.328 | 36,843.418 | 45,551% |
| 9 | Laravel | 80,131.451 | 80,043.315 | 98,961% |

**1,000 mixed routes — dispatch all (1,000 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | **Waypoint** | **32.464** | **20.707** | **100%** |
| 2 | FastRoute | 26.337 | 25.469 | 123% |
| 3 | Symfony | 3,327.506 | 3,344.249 | 16,151% |
| 4 | AltoRouter | 5,700.721 | 5,682.079 | 27,441% |
| 5 | Bramus | 7,031.994 | 7,081.422 | 34,199% |
| 6 | Nette | 40,070.953 | 40,166.404 | 193,979% |
| 7 | PHRoute | 41,235.299 | 41,513.010 | 200,482% |
| 8 | League | 61,723.837 | 61,783.328 | 298,375% |
| 9 | Laravel | 140,524.764 | 140,797.563 | 679,965% |

---

## Analysis and Conclusions

### Tier Classification

Based on steady-state request time (after warmup), where cacheable routers use their cache:

**Tier 1 — Fastest request handling** (compiled/cached dispatchers):
- **FastRoute** — fastest in 8 out of 9 scenarios. `cachedDispatcher()` loads compiled regex data from a PHP file, making warmup and request performance nearly identical. 0.012 ms per request at 100 static routes.
- **Waypoint** — prefix-trie matching with cache support. **Solidly 2nd place in every scenario, and 1st at 1,000 mixed routes** (20.7 ms vs FastRoute's 25.5 ms). Once the cache is loaded (warmup), subsequent request times are sub-millisecond for single dispatches. In this benchmark, Waypoint uses its core trie matcher directly (without PSR-15 middleware pipeline), just like FastRoute and Symfony use their core matchers.

**Tier 2 — Cached mid-range** (strong single-dispatch, but falls behind at scale):
- **Symfony** — `CompiledUrlMatcher` performs well for single dispatches (3rd in dynamic first-route at 0.056 ms), but drops to distant 3rd in all multi-dispatch scenarios. At 1,000 mixed routes, 161× slower than Waypoint (3,344 ms vs 20.7 ms).

**Tier 3 — Lightweight routers** (fast single dispatch, O(n) scaling):
- **AltoRouter** — fast at small scale, but linear O(n) matching causes dramatic slowdown at scale. At 1,000 mixed routes: 5,682 ms (274× Waypoint).
- **Bramus** — similar pattern to AltoRouter. Fast single-route dispatch but O(n) scaling costs it dearly: 7,081 ms at 1,000 mixed routes (342× Waypoint).

**Tier 4 — Non-cached mid-range**:
- **PHRoute** — built on FastRoute's core but no caching, so it pays full `simpleDispatcher()` cost per request.
- **Nette** — no caching, O(n) matching.

**Tier 5 — Heavy bootstrap**:
- **League** — PSR-15, no caching.
- **Laravel** — heaviest bootstrap. Carries full framework routing infrastructure (Container, Dispatcher, Events).

### Key Findings

#### 1. Waypoint takes 2nd place in every scenario (1st at 1,000 routes)

With warmup separated from request time, Waypoint's cached prefix-trie delivers sub-millisecond dispatching in all single-dispatch scenarios — **consistently 2nd place**, directly behind FastRoute:

| Scenario | FastRoute | **Waypoint** | 3rd place | Waypoint vs FastRoute |
|----------|-----------|-------------|-----------|----------------------|
| 100 static, first | 0.012 ms | **0.021 ms** | Symfony 0.063 ms | 1.8× |
| 100 static, last | 0.011 ms | **0.023 ms** | AltoRouter 0.810 ms | 2.1× |
| 100 dynamic, first | 0.017 ms | **0.033 ms** | Symfony 0.056 ms | 1.9× |
| 100 dynamic, last | 0.028 ms | **0.036 ms** | Symfony 0.710 ms | 1.3× |
| 100 static, all | 0.615 ms | **1.548 ms** | Symfony 38.916 ms | 2.5× |
| 100 dynamic, all | 1.612 ms | **2.763 ms** | Symfony 37.716 ms | 1.7× |
| 500 mixed, all | 8.201 ms | **10.410 ms** | Symfony 841.313 ms | 1.3× |
| 100x50 repeated | 80.884 ms | **137.371 ms** | Symfony 1,850.490 ms | 1.7× |
| **1000 mixed, all** | 25.469 ms | **20.707 ms** | Symfony 3,344.249 ms | **0.8× (Waypoint wins!)** |

The gap between Waypoint and FastRoute narrows as route count grows: from 2.5× at 100 static routes down to **Waypoint overtaking FastRoute** at 1,000 mixed routes.

#### 2. At 1,000 routes, Waypoint overtakes FastRoute

At 1,000 mixed routes, Waypoint's prefix-trie becomes faster than FastRoute's compiled regex:

- **Waypoint**: 20.707 ms (1st place)
- **FastRoute**: 25.469 ms (2nd place, 23% slower)

This proves that the prefix-trie data structure scales better than compiled regex at large route counts. FastRoute's regex groups grow linearly with route count, while Waypoint's trie provides near-constant-time lookups.

#### 3. Warmup reveals caching architecture

The warmup/request split clearly shows which routers benefit from caching and how their cache loading works:

| Router | Static first warmup | Static first request | Warmup/Request ratio |
|--------|-------------------|---------------------|---------------------|
| **Waypoint** | 1.872 ms | 0.021 ms | **89×** |
| FastRoute | 0.015 ms | 0.012 ms | 1.3× |
| Symfony | 0.093 ms | 0.063 ms | 1.5× |
| AltoRouter | 0.899 ms | 0.354 ms | 2.5× |
| Laravel | 41.002 ms | 12.095 ms | 3.4× |

Waypoint shows the largest warmup-to-request ratio (89×), meaning its first request carries significant cache-loading overhead, but subsequent requests are extremely fast. FastRoute's warmup is nearly identical to request time because its compiled cache is a simple PHP array `require`. For non-cacheable routers, warmup and request times are nearly identical — they pay the same initialization cost every time.

#### 4. Waypoint dramatically outperforms Symfony at scale

Symfony's `CompiledUrlMatcher` is a strong 3rd place in single dispatches (0.056–0.819 ms). But in multi-dispatch scenarios, Waypoint is an order of magnitude faster:

| Scenario | Waypoint | Symfony | Waypoint advantage |
|----------|----------|---------|-------------------|
| 100 static, all | 1.548 ms | 38.916 ms | **25.1× faster** |
| 100 dynamic, all | 2.763 ms | 37.716 ms | **13.7× faster** |
| 500 mixed, all | 10.410 ms | 841.313 ms | **80.8× faster** |
| 100x50 repeated | 137.371 ms | 1,850.490 ms | **13.5× faster** |
| 1000 mixed, all | 20.707 ms | 3,344.249 ms | **161.5× faster** |

At 1,000 routes, Waypoint is **161× faster** than Symfony. This demonstrates that Waypoint's cached prefix-trie is fundamentally more efficient for repeated dispatches than Symfony's compiled URL matcher.

#### 5. Waypoint crushes all lightweight routers at scale

The prefix-trie data structure gives Waypoint a massive structural advantage over O(n) matchers (AltoRouter, Bramus). While lightweight routers are competitive in single-dispatch tests, they cannot keep up with Waypoint at scale:

| Scenario | Waypoint | AltoRouter | Bramus |
|----------|----------|------------|--------|
| 100 static, all | **1.548 ms (2nd)** | 49.633 ms (4th) | 73.711 ms (5th) |
| 100 dynamic, all | **2.763 ms (2nd)** | 85.310 ms (5th) | 74.103 ms (4th) |
| 500 mixed, all | **10.410 ms (2nd)** | 1,434.145 ms (4th) | 1,784.132 ms (5th) |
| 100x50 repeated | **137.371 ms (2nd)** | 4,299.406 ms (5th) | 3,699.565 ms (4th) |
| 1000 mixed, all | **20.707 ms (1st!)** | 5,682.079 ms (4th) | 7,081.422 ms (5th) |

At 1,000 routes, Waypoint is **274× faster** than AltoRouter and **342× faster** than Bramus. The O(n) matchers' dispatch time scales linearly with route count, while Waypoint's trie-based cache provides near-constant-time lookups.

#### 6. Waypoint vs League: trie matcher vs PSR-7 pipeline

League is the only router in this benchmark that creates real PSR-7 Request/Response objects on every dispatch. Waypoint, like FastRoute and Symfony, uses its core matcher directly and returns raw strings. This architectural difference contributes to the gap:

| Scenario | Waypoint | League | Gap |
|----------|----------|--------|-----|
| 100 static, first | 0.021 ms | 6.117 ms | 291× faster |
| 100 dynamic, all | 2.763 ms | 737.192 ms | 267× faster |
| 500 mixed, all | 10.410 ms | 15,403.482 ms | 1,480× faster |
| 1000 mixed, all | 20.707 ms | 61,783.328 ms | **2,983× faster** |

The gap is partly explained by League's PSR-7 overhead (creating `ServerRequest`/`Response` on every dispatch) and partly by the lack of caching — League reinitializes and re-registers all routes on every request.

#### 7. Dynamic last-route dispatch: Waypoint nearly matches FastRoute

In dynamic last-route dispatch (the worst-case scenario for route matching), Waypoint takes **2nd place** at 0.036 ms — only 25% behind FastRoute (0.028 ms) and dramatically beating Symfony (0.710 ms), Bramus (0.996 ms), and AltoRouter (1.380 ms). This confirms that the prefix-trie provides consistently fast lookups regardless of route position, unlike O(n) matchers where last-route performance degrades.

#### 8. Laravel is consistently last

Laravel's full-framework routing infrastructure makes it the heaviest router. A single request with 100 static routes takes 12.095 ms (request time) — 1,008× slower than FastRoute. At 1,000 routes, a full dispatch cycle takes 140.8 seconds vs Waypoint's 20.7 ms. This is expected — Laravel's router is designed for a full-framework context where the bootstrap cost is amortized across the request lifecycle.

### Recommendations

| Use case | Recommended router |
|----------|--------------------|
| Maximum raw performance at ≤500 routes | **FastRoute** (with `cachedDispatcher`) |
| Best performance at large scale (1,000+ routes) | **Waypoint** (with cache) — beats FastRoute at 1,000 routes |
| Near-FastRoute performance with trie matching | **Waypoint** (with cache) — only 1.3–2.5× slower at ≤100 routes |
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
- Results are from a **single run** on 2026-02-18, PHP 8.4.5, Linux
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
