<?php

declare(strict_types=1);

namespace WaypointBench\Cli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WaypointBench\Adapter\AdapterInterface;
use WaypointBench\Adapter\AltoRouterAdapter;
use WaypointBench\Adapter\BramusAdapter;
use WaypointBench\Adapter\CacheableAdapterInterface;
use WaypointBench\Adapter\FastRouteAdapter;
use WaypointBench\Adapter\LaravelAdapter;
use WaypointBench\Adapter\LeagueAdapter;
use WaypointBench\Adapter\NetteAdapter;
use WaypointBench\Adapter\PHRouteAdapter;
use WaypointBench\Adapter\SymfonyAdapter;
use WaypointBench\Adapter\WaypointAdapter;
use WaypointBench\RouteSet\RouteDefinition;
use WaypointBench\RouteSet\RouteGenerator;

/**
 * CLI benchmark command that measures real request-response lifecycle.
 *
 * Every scenario simulates production behavior:
 * - Cacheable routers: cache is warmed once (setup), then each "request" loads from cache + dispatches
 * - Non-cacheable routers: each "request" initializes + registers routes + dispatches
 *
 * All routers compete in every scenario on equal terms.
 */
#[AsCommand(
    name: 'benchmark',
    description: 'Run router benchmarks and display comparison table',
)]
final class BenchmarkCommand extends Command
{
    private const int DEFAULT_RUNS = 20;

    protected function configure(): void
    {
        $this
            ->addOption('router', 'r', InputOption::VALUE_OPTIONAL, 'Comma-separated list of router names to test')
            ->addOption('scenario', 's', InputOption::VALUE_OPTIONAL, 'Comma-separated list of scenarios (static,dynamic,highload)')
            ->addOption('runs', null, InputOption::VALUE_OPTIONAL, 'Number of runs per test (default: 20)', (string) self::DEFAULT_RUNS);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $runs = (int) $input->getOption('runs');

        $io->title('Waypoint Router Benchmark');
        $io->text(\sprintf('PHP %s | %s | %s', PHP_VERSION, PHP_OS, date('Y-m-d H:i:s')));
        $io->text(\sprintf('Runs per test: %d (using median)', $runs));
        $io->text('Each request = full router lifecycle (init/cache-load + dispatch)');
        $io->newLine();

        $adapters = $this->getAdapters($input);
        $scenarios = $this->getScenarios($input);

        foreach ($scenarios as $scenarioName => $scenarioFn) {
            $io->section($scenarioName);

            $results = [];

            $setupFn = $scenarioFn['setup'];
            $runFn = $scenarioFn['run'];
            $teardownFn = $scenarioFn['teardown'] ?? null;

            foreach ($adapters as $adapter) {
                $name = $adapter->getName();

                try {
                    // Setup phase (warm cache) — outside timing loop
                    $setupFn($adapter);

                    $timings = [];
                    $peakMemory = 0;

                    for ($run = 0; $run < $runs; $run++) {
                        $memBefore = memory_get_usage(true);
                        $start = hrtime(true);

                        $runFn($adapter);

                        $elapsed = (hrtime(true) - $start) / 1_000_000; // ms
                        $memAfter = memory_get_peak_usage(true);

                        $timings[] = $elapsed;
                        $peakMemory = max($peakMemory, $memAfter - $memBefore);
                    }

                    // Teardown phase (clear cache) — after timing loop
                    if ($teardownFn !== null) {
                        $teardownFn($adapter);
                    }

                    sort($timings);
                    $median = $timings[(int) floor(\count($timings) / 2)];

                    $results[] = [
                        'name' => $name,
                        'time' => $median,
                        'memory' => $peakMemory,
                        'error' => null,
                    ];
                } catch (\Throwable $e) {
                    // Ensure cleanup even on error
                    if ($teardownFn !== null) {
                        try {
                            $teardownFn($adapter);
                        } catch (\Throwable) {
                        }
                    }

                    $results[] = [
                        'name' => $name,
                        'time' => PHP_FLOAT_MAX,
                        'memory' => 0,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            $this->renderTable($io, $results);
        }

        $io->success('Benchmark complete.');

        // Clean up cache directory
        $this->cleanupCacheDir();

        return Command::SUCCESS;
    }

    /**
     * @return AdapterInterface[]
     */
    private function getAdapters(InputInterface $input): array
    {
        $all = [
            new WaypointAdapter(),
            new FastRouteAdapter(),
            new SymfonyAdapter(),
            new LaravelAdapter(),
            new LeagueAdapter(),
            new NetteAdapter(),
            new BramusAdapter(),
            new AltoRouterAdapter(),
            new PHRouteAdapter(),
        ];

        $filter = $input->getOption('router');

        if ($filter === null) {
            return $all;
        }

        $names = array_map('trim', explode(',', strtolower($filter)));

        return array_filter(
            $all,
            static fn (AdapterInterface $a) => \in_array(strtolower($a->getName()), $names, true),
        );
    }

    /**
     * @return array<string, array{setup: \Closure, run: \Closure, teardown?: \Closure}>
     */
    private function getScenarios(InputInterface $input): array
    {
        $all = [
            'static' => [
                'Static Routes: 100 routes, dispatch first' => $this->scenarioDispatchFirst(
                    RouteGenerator::staticRoutes(100),
                ),
                'Static Routes: 100 routes, dispatch last' => $this->scenarioDispatchLast(
                    RouteGenerator::staticRoutes(100),
                ),
                'Static Routes: 100 routes, dispatch all' => $this->scenarioDispatchAll(
                    RouteGenerator::staticRoutes(100),
                ),
            ],
            'dynamic' => [
                'Dynamic Routes: 100 routes, dispatch first' => $this->scenarioDispatchFirst(
                    RouteGenerator::dynamicRoutes(100),
                ),
                'Dynamic Routes: 100 routes, dispatch last' => $this->scenarioDispatchLast(
                    RouteGenerator::dynamicRoutes(100),
                ),
                'Dynamic Routes: 100 routes, dispatch all' => $this->scenarioDispatchAll(
                    RouteGenerator::dynamicRoutes(100),
                ),
            ],
            'highload' => [
                'High-Load: 500 mixed routes, dispatch all' => $this->scenarioDispatchAll(
                    RouteGenerator::mixedRoutes(500),
                ),
                'High-Load: 100 dynamic x50 repeated' => $this->scenarioRepeatedDispatch(
                    RouteGenerator::dynamicRoutes(100),
                    50,
                ),
                'Large-Scale: 1000 mixed routes, dispatch all' => $this->scenarioDispatchAll(
                    RouteGenerator::mixedRoutes(1000),
                ),
            ],
        ];

        $filter = $input->getOption('scenario');

        if ($filter === null) {
            return array_merge(...array_values($all));
        }

        $names = array_map('trim', explode(',', strtolower($filter)));
        $result = [];

        foreach ($names as $name) {
            if (isset($all[$name])) {
                $result = array_merge($result, $all[$name]);
            }
        }

        return $result;
    }

    // ── Scenario builders ───────────────────────────────────────────────

    /**
     * Single request: boot router + dispatch first route.
     *
     * @param RouteDefinition[] $routes
     * @return array{setup: \Closure, run: \Closure, teardown: \Closure}
     */
    private function scenarioDispatchFirst(array $routes): array
    {
        $cacheDir = $this->getCacheDir();
        $first = $routes[0];

        return [
            'setup' => $this->makeSetup($routes, $cacheDir),
            'run' => function (AdapterInterface $adapter) use ($routes, $cacheDir, $first): void {
                $this->bootAdapter($adapter, $routes, $cacheDir);
                $adapter->dispatch($first->method, $first->testUri);
            },
            'teardown' => $this->makeTeardown($cacheDir),
        ];
    }

    /**
     * Single request: boot router + dispatch last route.
     *
     * @param RouteDefinition[] $routes
     * @return array{setup: \Closure, run: \Closure, teardown: \Closure}
     */
    private function scenarioDispatchLast(array $routes): array
    {
        $cacheDir = $this->getCacheDir();
        $last = $routes[array_key_last($routes)];

        return [
            'setup' => $this->makeSetup($routes, $cacheDir),
            'run' => function (AdapterInterface $adapter) use ($routes, $cacheDir, $last): void {
                $this->bootAdapter($adapter, $routes, $cacheDir);
                $adapter->dispatch($last->method, $last->testUri);
            },
            'teardown' => $this->makeTeardown($cacheDir),
        ];
    }

    /**
     * N requests: for each route — boot router + dispatch.
     *
     * @param RouteDefinition[] $routes
     * @return array{setup: \Closure, run: \Closure, teardown: \Closure}
     */
    private function scenarioDispatchAll(array $routes): array
    {
        $cacheDir = $this->getCacheDir();

        return [
            'setup' => $this->makeSetup($routes, $cacheDir),
            'run' => function (AdapterInterface $adapter) use ($routes, $cacheDir): void {
                foreach ($routes as $route) {
                    $this->bootAdapter($adapter, $routes, $cacheDir);
                    $adapter->dispatch($route->method, $route->testUri);
                }
            },
            'teardown' => $this->makeTeardown($cacheDir),
        ];
    }

    /**
     * N * M requests: repeated dispatching of all routes (boot per request).
     *
     * @param RouteDefinition[] $routes
     * @return array{setup: \Closure, run: \Closure, teardown: \Closure}
     */
    private function scenarioRepeatedDispatch(array $routes, int $repeats): array
    {
        $cacheDir = $this->getCacheDir();

        return [
            'setup' => $this->makeSetup($routes, $cacheDir),
            'run' => function (AdapterInterface $adapter) use ($routes, $cacheDir, $repeats): void {
                for ($i = 0; $i < $repeats; $i++) {
                    foreach ($routes as $route) {
                        $this->bootAdapter($adapter, $routes, $cacheDir);
                        $adapter->dispatch($route->method, $route->testUri);
                    }
                }
            },
            'teardown' => $this->makeTeardown($cacheDir),
        ];
    }

    // ── Lifecycle helpers ───────────────────────────────────────────────

    /**
     * Create a setup closure that warms cache for cacheable adapters.
     *
     * @param RouteDefinition[] $routes
     */
    private function makeSetup(array $routes, string $cacheDir): \Closure
    {
        return static function (AdapterInterface $adapter) use ($routes, $cacheDir): void {
            if ($adapter instanceof CacheableAdapterInterface) {
                $adapter->warmCache($routes, $cacheDir);
            }
        };
    }

    /**
     * Create a teardown closure that clears cache for cacheable adapters.
     */
    private function makeTeardown(string $cacheDir): \Closure
    {
        return static function (AdapterInterface $adapter) use ($cacheDir): void {
            if ($adapter instanceof CacheableAdapterInterface) {
                $adapter->clearCache($cacheDir);
            }
        };
    }

    /**
     * Boot the adapter for a single request.
     *
     * Cacheable adapters load from pre-warmed cache (fast path).
     * Non-cacheable adapters initialize and register routes from scratch.
     *
     * @param RouteDefinition[] $routes
     */
    private function bootAdapter(AdapterInterface $adapter, array $routes, string $cacheDir): void
    {
        if ($adapter instanceof CacheableAdapterInterface) {
            $adapter->initializeFromCache($cacheDir);
        } else {
            $adapter->initialize();
            $adapter->registerRoutes($routes);
        }
    }

    private function getCacheDir(): string
    {
        $dir = \dirname(__DIR__, 2) . '/var/cache';
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(\sprintf('Directory "%s" was not created', $dir));
        }

        return $dir;
    }

    private function cleanupCacheDir(): void
    {
        $dir = $this->getCacheDir();
        if (is_dir($dir)) {
            $files = glob($dir . '/*');
            if ($files !== false) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }
            rmdir($dir);
        }
    }

    /**
     * @param array<array{name: string, time: float, memory: int, error: string|null}> $results
     */
    private function renderTable(SymfonyStyle $io, array $results): void
    {
        // Sort by time
        usort($results, static fn ($a, $b) => $a['time'] <=> $b['time']);

        $fastest = $results[0]['time'];
        $lowestMem = min(array_column($results, 'memory'));

        $table = new Table($io);
        $table->setHeaders(['Rank', 'Router', 'Time (ms)', 'Time (%)', 'Peak Memory (MB)', 'Memory (%)']);

        $rank = 1;
        foreach ($results as $result) {
            if ($result['error'] !== null) {
                $table->addRow([
                    $rank++,
                    $result['name'],
                    'ERROR',
                    '-',
                    '-',
                    substr($result['error'], 0, 40),
                ]);

                continue;
            }

            $timePct = $fastest > 0 ? ($result['time'] / $fastest) * 100 : 100;
            $memMb = $result['memory'] / 1024 / 1024;
            $memPct = $lowestMem > 0 ? ($result['memory'] / $lowestMem) * 100 : 100;

            $table->addRow([
                $rank++,
                $result['name'],
                number_format($result['time'], 3),
                number_format($timePct, 0) . '%',
                number_format($memMb, 3),
                number_format($memPct, 0) . '%',
            ]);
        }

        $table->render();
        $io->newLine();
    }
}
