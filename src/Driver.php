<?php

namespace Simples\Persistence;

/**
 * Interface Driver
 * @package Simples\Persistence
 */
interface Driver
{
    /**
     * @return string
     */
    public function scope(): string;

    /**
     * @return bool
     */
    public function start(): bool;

    /**
     * @return bool
     */
    public function commit(): bool;

    /**
     * @return bool
     */
    public function rollback(): bool;

    /**
     * @param array $clausules
     * @param array $values
     * @return string
     */
    public function create(array $clausules, array $values): string;

    /**
     * @param array $clausules
     * @param array $values
     * @return array
     */
    public function read(array $clausules, array $values = []): array;

    /**
     * @param array $clausules
     * @param array $values
     * @param array $filters
     * @return int
     */
    public function update(array $clausules, array $values, array $filters): int;

    /**
     * @param array $clausules
     * @param array $values
     * @return int
     */
    public function destroy(array $clausules, array $values): int;

    /**
     * @param string $instruction
     * @param array $values
     * @return int
     */
    public function run(string $instruction, array $values = []): int;

    /**
     * @param string $instruction
     * @param array $values
     * @return array
     */
    public function query(string $instruction, array $values = []): array;
}
