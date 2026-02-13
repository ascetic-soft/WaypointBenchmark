<?php

declare(strict_types=1);

namespace WaypointBench\Cli;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use WaypointBench\Adapter\AdapterInterface;
use WaypointBench\Adapter\AltoRouterAdapter;
use WaypointBench\Adapter\BramusAdapter;
use WaypointBench\Adapter\FastRouteAdapter;
use WaypointBench\Adapter\LaravelAdapter;
use WaypointBench\Adapter\LeagueAdapter;
use WaypointBench\Adapter\NetteAdapter;
use WaypointBench\Adapter\PHRouteAdapter;
use WaypointBench\Adapter\SymfonyAdapter;
use WaypointBench\Adapter\WaypointAdapter;
use WaypointBench\RouteSet\RouteDefinition;
use WaypointBench\RouteSet\RouteGenerator;

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
            ->addOption('scenario', 's', InputOption::VALUE_OPTIONAL, 'Comma-separated list of scenarios (static,dynamic,registration,highload)')
            ->addOption('runs', null, InputOption::VALUE_OPTIONAL, 'Number of runs per test (default: 20)', (string) self::DEFAULT_RUNS);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $runs = (int) $input->getOption('runs');

        $io->title('Waypoint Router Benchmark');
        $io->text(sprintf('PHP %s | %s | %s', PHP_VERSION, PHP_OS, date('Y-m-d H:i:s')));
        $io->text(sprintf('Runs per test: %d (using median)', $runs));
        $io->newLine();

        $adapters = $this->getAdapters($input);
        $scenarios = $this->getScenarios($input);

        foreach ($scenarios as $scenarioName => $scenarioFn) {
            $io->section($scenarioName);

            $results = [];

            foreach ($adapters as $adapter) {
                $name = $adapter->getName();

                try {
                    $timings = [];
                    $peakMemory = 0;

                    for ($run = 0; $run < $runs; $run++) {
                        $memBefore = memory_get_usage(true);
                        $start = hrtime(true);

                        $scenarioFn($adapter);

                        $elapsed = (hrtime(true) - $start) / 1_000_000; // ms
                        $memAfter = memory_get_peak_usage(true);

                        $timings[] = $elapsed;
                        $peakMemory = max($peakMemory, $memAfter - $memBefore);
                    }

                    sort($timings);
                    $median = $timings[(int) floor(count($timings) / 2)];

                    $results[] = [
                        'name' => $name,
                        'time' => $median,
                        'memory' => $peakMemory,
                        'error' => null,
                    ];
                } catch (\Throwable $e) {
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
            static fn (AdapterInterface $a) => in_array(strtolower($a->getName()), $names, true),
        );
    }

    /**
     * @return array<string, \Closure>
     */
    private function getScenarios(InputInterface $input): array
    {
        $all = [
            'static' => [
                'Static Routes: Register 100, Dispatch First' => $this->scenarioDispatchFirst(
                    RouteGenerator::staticRoutes(100),
                ),
                'Static Routes: Register 100, Dispatch Last' => $this->scenarioDispatchLast(
                    RouteGenerator::staticRoutes(100),
                ),
                'Static Routes: Register 100, Dispatch All' => $this->scenarioDispatchAll(
                    RouteGenerator::staticRoutes(100),
                ),
            ],
            'dynamic' => [
                'Dynamic Routes: Register 100, Dispatch First' => $this->scenarioDispatchFirst(
                    RouteGenerator::dynamicRoutes(100),
                ),
                'Dynamic Routes: Register 100, Dispatch Last' => $this->scenarioDispatchLast(
                    RouteGenerator::dynamicRoutes(100),
                ),
                'Dynamic Routes: Register 100, Dispatch All' => $this->scenarioDispatchAll(
                    RouteGenerator::dynamicRoutes(100),
                ),
            ],
            'registration' => [
                'Registration: 100 Static Routes' => $this->scenarioRegisterOnly(
                    RouteGenerator::staticRoutes(100),
                ),
                'Registration: 500 Mixed Routes' => $this->scenarioRegisterOnly(
                    RouteGenerator::mixedRoutes(500),
                ),
                'Registration: 1000 Mixed Routes' => $this->scenarioRegisterOnly(
                    RouteGenerator::mixedRoutes(1000),
                ),
            ],
            'highload' => [
                'High-Load: 500 Mixed Routes, Dispatch All' => $this->scenarioDispatchAll(
                    RouteGenerator::mixedRoutes(500),
                ),
                'High-Load: 100 Dynamic x50 Repeated' => $this->scenarioRepeatedDispatch(
                    RouteGenerator::dynamicRoutes(100),
                    50,
                ),
                'Large-Scale: 1000 Mixed Routes, Dispatch All' => $this->scenarioDispatchAll(
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

    /**
     * @param RouteDefinition[] $routes
     */
    private function scenarioDispatchFirst(array $routes): \Closure
    {
        return static function (AdapterInterface $adapter) use ($routes): void {
            $adapter->initialize();
            $adapter->registerRoutes($routes);

            $first = $routes[0];
            $adapter->dispatch($first->method, $first->testUri);
        };
    }

    /**
     * @param RouteDefinition[] $routes
     */
    private function scenarioDispatchLast(array $routes): \Closure
    {
        return static function (AdapterInterface $adapter) use ($routes): void {
            $adapter->initialize();
            $adapter->registerRoutes($routes);

            $last = $routes[array_key_last($routes)];
            $adapter->dispatch($last->method, $last->testUri);
        };
    }

    /**
     * @param RouteDefinition[] $routes
     */
    private function scenarioDispatchAll(array $routes): \Closure
    {
        return static function (AdapterInterface $adapter) use ($routes): void {
            $adapter->initialize();
            $adapter->registerRoutes($routes);

            foreach ($routes as $route) {
                $adapter->dispatch($route->method, $route->testUri);
            }
        };
    }

    /**
     * @param RouteDefinition[] $routes
     */
    private function scenarioRegisterOnly(array $routes): \Closure
    {
        return static function (AdapterInterface $adapter) use ($routes): void {
            $adapter->initialize();
            $adapter->registerRoutes($routes);
        };
    }

    /**
     * @param RouteDefinition[] $routes
     */
    private function scenarioRepeatedDispatch(array $routes, int $repeats): \Closure
    {
        return static function (AdapterInterface $adapter) use ($routes, $repeats): void {
            $adapter->initialize();
            $adapter->registerRoutes($routes);

            for ($i = 0; $i < $repeats; $i++) {
                foreach ($routes as $route) {
                    $adapter->dispatch($route->method, $route->testUri);
                }
            }
        };
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
