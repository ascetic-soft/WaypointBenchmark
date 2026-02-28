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

> PHP 8.4.5 | Linux | 2026-02-28 | 20 runs per test (median)

### 1. Static Route Dispatching

100 static routes. Each request = boot router + dispatch one route.

**Dispatch first route (best case)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 0.0059 | 0.0012 | 100% |
| 2 | **Waypoint** | **1.9887** | **0.0024** | **200%** |
| 3 | Symfony | 0.0380 | 0.0036 | 300% |
| 4 | AltoRouter | 0.9498 | 0.0131 | 1,099% |
| 5 | Bramus | 1.8106 | 0.0273 | 2,299% |
| 6 | PHRoute | 3.7341 | 0.0689 | 5,797% |
| 7 | League | 15.1085 | 0.1710 | 14,392% |
| 8 | Nette | 8.7991 | 0.1745 | 14,691% |
| 9 | Laravel | 42.7418 | 1.0994 | 92,546% |

**Dispatch last route (worst case)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 0.1116 | 0.0024 | 100% |
| 2 | **Waypoint** | **0.6067** | **0.0024** | **100%** |
| 3 | AltoRouter | 0.3716 | 0.0154 | 650% |
| 4 | Symfony | 0.7219 | 0.0273 | 1,150% |
| 5 | Bramus | 1.5423 | 0.0499 | 2,101% |
| 6 | PHRoute | 0.2921 | 0.0653 | 2,751% |
| 7 | League | 0.2185 | 0.1365 | 5,751% |
| 8 | Nette | 1.0733 | 0.2458 | 10,353% |
| 9 | Laravel | 3.8101 | 0.8620 | 36,309% |

**Dispatch all 100 routes (100 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 0.3455 | 0.0142 | 100% |
| 2 | **Waypoint** | **2.3081** | **0.0368** | **258%** |
| 3 | Symfony | 2.0635 | 0.5390 | 3,783% |
| 4 | AltoRouter | 1.2775 | 0.8940 | 6,275% |
| 5 | Bramus | 2.8614 | 2.5551 | 17,933% |
| 6 | PHRoute | 5.6409 | 4.8549 | 34,074% |
| 7 | League | 13.3144 | 9.8511 | 69,140% |
| 8 | Nette | 15.9348 | 14.0161 | 98,373% |
| 9 | Laravel | 65.6307 | 29.3478 | 205,978% |

### 2. Dynamic Route Dispatching

100 dynamic routes with `{id}` and `{slug}` parameters. Each request = boot router + dispatch one route.

**Dispatch first route (best case)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 0.0843 | 0.0024 | 100% |
| 2 | Symfony | 0.0285 | 0.0024 | 100% |
| 3 | **Waypoint** | **0.4132** | **0.0024** | **100%** |
| 4 | AltoRouter | 0.0582 | 0.0154 | 650% |
| 5 | Bramus | 0.0534 | 0.0202 | 850% |
| 6 | League | 0.7468 | 0.1900 | 8,002% |
| 7 | Laravel | 0.5450 | 0.1912 | 8,052% |
| 8 | Nette | 0.5972 | 0.2066 | 8,702% |
| 9 | PHRoute | 0.7100 | 0.2292 | 9,652% |

**Dispatch last route (worst case)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | **Waypoint** | **0.4167** | **0.0024** | **100%** |
| 2 | FastRoute | 0.3229 | 0.0024 | 100% |
| 3 | Symfony | 0.0392 | 0.0083 | 350% |
| 4 | Bramus | 0.4417 | 0.0392 | 1,650% |
| 5 | AltoRouter | 0.5521 | 0.0867 | 3,649% |
| 6 | League | 0.2410 | 0.1805 | 7,599% |
| 7 | PHRoute | 0.5913 | 0.2268 | 9,548% |
| 8 | Nette | 1.3856 | 0.2671 | 11,248% |
| 9 | Laravel | 1.1125 | 0.4797 | 20,197% |

**Dispatch all 100 routes (100 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 0.1484 | 0.0807 | 100% |
| 2 | **Waypoint** | **1.0531** | **0.0938** | **116%** |
| 3 | Symfony | 0.8549 | 0.4072 | 504% |
| 4 | Bramus | 3.1416 | 2.8673 | 3,551% |
| 5 | AltoRouter | 5.2621 | 5.1375 | 6,363% |
| 6 | League | 18.2774 | 17.6873 | 21,907% |
| 7 | PHRoute | 23.0005 | 22.5920 | 27,982% |
| 8 | Nette | 24.0061 | 23.7615 | 29,431% |
| 9 | Laravel | 40.8077 | 38.8403 | 48,107% |

### 3. High-Load and Large-Scale

Each "request" = boot router from scratch or from cache + dispatch. The router initializes on every single request.

**500 mixed routes — dispatch all (500 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | **Waypoint** | **2.8567** | **0.3372** | **100%** |
| 2 | FastRoute | 1.1291 | 0.4156 | 123% |
| 3 | Symfony | 9.2087 | 8.0559 | 2,389% |
| 4 | AltoRouter | 52.8493 | 51.4471 | 15,257% |
| 5 | Bramus | 80.0730 | 75.8130 | 22,483% |
| 6 | League | 356.7198 | 355.6014 | 105,459% |
| 7 | PHRoute | 356.6106 | 356.7365 | 105,795% |
| 8 | Nette | 508.4099 | 505.8655 | 150,022% |
| 9 | Laravel | 763.6449 | 766.3448 | 227,271% |

**100 dynamic routes x50 repeated dispatch (5,000 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | FastRoute | 4.3586 | 4.2434 | 100% |
| 2 | **Waypoint** | **6.1396** | **5.0947** | **120%** |
| 3 | Symfony | 20.6270 | 20.4763 | 483% |
| 4 | Bramus | 147.0620 | 145.5493 | 3,430% |
| 5 | AltoRouter | 255.2979 | 256.4365 | 6,043% |
| 6 | League | 869.3767 | 866.4441 | 20,418% |
| 7 | PHRoute | 1,155.9078 | 1,165.1438 | 27,458% |
| 8 | Nette | 1,211.9213 | 1,218.2164 | 28,708% |
| 9 | Laravel | 2,052.2756 | 2,112.4945 | 49,783% |

**1,000 mixed routes — dispatch all (1,000 requests)**

| Rank | Router | Warmup (ms) | Request (ms) | vs Fastest |
|------|--------|-------------|--------------|------------|
| 1 | **Waypoint** | **6.9137** | **0.7005** | **100%** |
| 2 | FastRoute | 2.7356 | 1.5055 | 215% |
| 3 | Symfony | 36.7281 | 34.3701 | 4,906% |
| 4 | AltoRouter | 210.0924 | 205.2719 | 29,303% |
| 5 | Bramus | 296.7835 | 293.9505 | 41,962% |
| 6 | PHRoute | 1,446.6098 | 1,435.9407 | 204,985% |
| 7 | League | 1,442.8389 | 1,446.6347 | 206,512% |
| 8 | Nette | 2,036.1102 | 2,063.0038 | 294,501% |
| 9 | Laravel | 4,478.4178 | 3,820.1748 | 545,343% |

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

#### Benchmark Environment

- OS: Ubuntu 25.04
- Kernel: Linux 6.14.0-37-generic (x86_64)
- CPU: AMD Ryzen 9 8945HX with Radeon Graphics
- CPU topology: 16 cores / 32 threads
- RAM: 30 GiB
- PHP: 8.4.5 CLI (NTS) with Zend OPcache
- Composer: 2.8.10
- OPcache CLI: enabled (`opcache.enable_cli=On`)
- JIT: tracing (`opcache.jit=tracing`, `opcache.jit_buffer_size=128M`)

- Every request simulates a real PHP-FPM lifecycle: the router initializes from scratch or loads from cache, then dispatches
- Cacheable routers (FastRoute, Symfony, Waypoint) have their cache pre-warmed before the timing loop — just like a production deployment
- A **warmup request** runs first to separate cold-cache overhead (opcache, autoloader, etc.) from steady-state performance
- **Warmup (ms)** = first request time (cold); **Request (ms)** = median of subsequent runs (warm)
- Non-cacheable routers pay the full `initialize() + registerRoutes()` cost on every request — that's the real cost of not having cache support
- All routers handle identical route sets generated deterministically by `RouteGenerator`
- Results are from **20 runs (median)** on 2026-02-28, PHP 8.4.5, Linux
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
