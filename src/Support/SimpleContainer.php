<?php

declare(strict_types=1);

namespace WaypointBench\Support;

use Psr\Container\ContainerInterface;

/**
 * Minimal PSR-11 container for routers that require one.
 */
final class SimpleContainer implements ContainerInterface
{
    /** @var array<string, mixed> */
    private array $entries = [];

    public function set(string $id, mixed $value): void
    {
        $this->entries[$id] = $value;
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new \RuntimeException("Entry not found: $id");
        }

        return $this->entries[$id];
    }

    public function has(string $id): bool
    {
        return \array_key_exists($id, $this->entries);
    }
}
