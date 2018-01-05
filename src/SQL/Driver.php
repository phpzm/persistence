<?php

namespace Simples\Persistence\SQL;

use PDO;
use Simples\Persistence\Driver as Persistence;
use Simples\Persistence\Error\SimplesPersistenceError;
use Simples\Persistence\FilterMap;
use Simples\Persistence\SQL\Error\SimplesPersistenceDataError;
use Simples\Persistence\SQL\Operations\Create;
use Simples\Persistence\SQL\Operations\Destroy;
use Simples\Persistence\SQL\Operations\Read;
use Simples\Persistence\SQL\Operations\Update;
use stdClass;
use Throwable;

/**
 * Class SQLDriver
 * @package Simples\Persistence
 */
abstract class Driver extends Connection implements Persistence
{
    /**
     * @trait Operations
     */
    use Modifiers, Create, Read, Update, Destroy;

    /**
     * SQLDriver constructor.
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        parent::__construct($settings);

        $this->filters($this->scope);
    }

    /**
     * @return bool
     */
    public function start(): bool
    {
        return $this->connection()->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit(): bool
    {
        return $this->connection()->commit();
    }

    /**
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->connection()->rollBack();
    }

    /**
     * @param array $clausules
     * @param array $values
     * @return string
     * @throws SimplesPersistenceDataError
     * @throws SimplesPersistenceError
     */
    public function create(array $clausules, array $values): string
    {
        $sql = $this->getInsert($clausules);
        $parameters = array_values($values);
        $this->addLog($sql, $parameters, off($clausules, 'log', false));
        $statement = $this->statement($sql);
        try {
            if ($statement && $statement->execute($parameters)) {
                return (string)$this->connection()->lastInsertId();
            }
        } catch (Throwable $error) {
            throw new SimplesPersistenceError([$sql, $parameters, $statement->errorInfo()], [$error]);
        }
        throw new SimplesPersistenceDataError([$statement->errorInfo()], [$sql, $parameters]);
    }

    /**
     * @param array $clausules
     * @param array $values
     * @return array
     * @throws SimplesPersistenceDataError
     * @throws SimplesPersistenceError
     */
    public function read(array $clausules, array $values = []): array
    {
        $sql = $this->getSelect($clausules);
        $parameters = array_values($values);
        $this->addLog($sql, $parameters, off($clausules, 'log', false));
        $statement = $this->statement($sql);
        try {
            $fetch = off($clausules, 'fetch', PDO::FETCH_ASSOC);
            if ($fetch === stdClass::class) {
                $fetch = PDO::FETCH_OBJ;
            }

            if ($statement && $statement->execute($parameters)) {
                return $statement->fetchAll($fetch);
            }
        } catch (Throwable $error) {
            throw new SimplesPersistenceError([$sql, $parameters], [$error]);
        }
        throw new SimplesPersistenceDataError([$statement->errorInfo()], [$sql, $parameters]);
    }

    /**
     * @param array $clausules
     * @param array $values
     * @param array $filters
     * @return int
     * @throws SimplesPersistenceDataError
     * @throws SimplesPersistenceError
     */
    public function update(array $clausules, array $values, array $filters): int
    {
        $sql = $this->getUpdate($clausules);
        $parameters = array_merge(array_values($values), array_values($filters));
        $this->addLog($sql, $parameters, off($clausules, 'log', false));
        $statement = $this->statement($sql);
        try {
            if ($statement && $statement->execute($parameters)) {
                return $statement->rowCount();
            }
        } catch (Throwable $error) {
            throw new SimplesPersistenceError([$sql, $parameters], [$error]);
        }
        throw new SimplesPersistenceDataError([$statement->errorInfo()], [$sql, $parameters]);
    }

    /**
     * @param array $clausules
     * @param array $values
     * @return int
     * @throws SimplesPersistenceDataError
     * @throws SimplesPersistenceError
     */
    public function destroy(array $clausules, array $values): int
    {
        $sql = $this->getDelete($clausules);
        $parameters = array_values($values);
        $this->addLog($sql, $values, off($clausules, 'log', false));
        $statement = $this->statement($sql);
        try {
            if ($statement && $statement->execute($parameters)) {
                return $statement->rowCount();
            }
        } catch (Throwable $error) {
            throw new SimplesPersistenceError([$sql, $parameters], [$error]);
        }
        throw new SimplesPersistenceDataError([$statement->errorInfo()], [$sql, $parameters]);
    }

    /**
     * @param string $instruction
     * @param array $values
     * @return int
     * @throws SimplesPersistenceDataError
     * @throws SimplesPersistenceError
     */
    public function run(string $instruction, array $values = []): int
    {
        $this->addLog($instruction, $values);
        $statement = $this->statement($instruction);
        try {
            if ($statement && $statement->execute($values)) {
                return $statement->rowCount();
            }
        } catch (Throwable $error) {
            throw new SimplesPersistenceError([$instruction, $values], [$error]);
        }
        throw new SimplesPersistenceDataError([$statement->errorInfo()], [$instruction, $values]);
    }


    /**
     * @param string $instruction
     * @param array $values
     * @return array
     * @throws SimplesPersistenceDataError
     * @throws SimplesPersistenceError
     */
    public function query(string $instruction, array $values = []): array
    {
        $this->addLog($instruction, $values);
        $statement = $this->statement($instruction);
        try {
            if ($statement && $statement->execute($values)) {
                return $statement->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $error) {
            throw new SimplesPersistenceError([$instruction, $values], [$error]);
        }
        throw new SimplesPersistenceDataError([$statement->errorInfo()], [$instruction, $values]);
    }

    /**
     * @param string $scope
     */
    protected function filters(string $scope)
    {
        $getValue = function ($value) {
            return $value;
        };

        FilterMap::add($scope, 'equal', $getValue, function ($name) {
            return "{$name} = ?";
        });

        FilterMap::add($scope, 'not', $getValue, function ($name) {
            return "{$name} <> ?";
        });

        FilterMap::add($scope, 'blank', $getValue, function ($name) {
            return "({$name} IS NULL) OR (NOT {$name})";
        });

        FilterMap::add($scope, 'less-equal-than', $getValue, function ($name) {
            return "{$name} < ?";
        });

        FilterMap::add($scope, 'less-than', $getValue, function ($name) {
            return "{$name} < ?";
        });

        FilterMap::add($scope, 'greater-than', $getValue, function ($name) {
            return "{$name} > ?";
        });

        FilterMap::add($scope, 'greater-equal-than', $getValue, function ($name) {
            return "{$name} >= ?";
        });

        FilterMap::add($scope, 'in', function ($value) {
            return explode('|', $value);
        }, function ($name, $value) {
            $keys = implode(',', array_map(function () {
                return '?';
            }, explode('|', $value)));
            return "{$name} IN ({$keys})";
        });
    }
}
