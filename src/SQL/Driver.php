<?php

namespace Simples\Persistence\SQL;

use PDO;
use Simples\Error\SimplesRunTimeError;
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
     * @trait Modifiers, Create, Read, Update, Destroy, Filters
     */
    use Modifiers, Create, Read, Update, Destroy, Filters;

    /**
     * SQLDriver constructor.
     * @param array $settings
     * @throws SimplesRunTimeError
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
        try {
            $statement = $this->statement($sql);

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
     * @throws SimplesPersistenceError
     */
    public function read(array $clausules, array $values = []): array
    {
        $sql = $this->getSelect($clausules);
        $parameters = array_values($values);
        $this->addLog($sql, $parameters, off($clausules, 'log', false));
        try {
            $statement = $this->statement($sql);

            $fetch = off($clausules, 'fetch', PDO::FETCH_ASSOC);
            if ($fetch === stdClass::class) {
                $fetch = PDO::FETCH_OBJ;
            }
            if (!$statement) {
                throw new SimplesPersistenceError([$sql, $parameters]);
            }
            $statement->execute($parameters);
            // throw new SimplesPersistenceDataError([$statement->errorInfo()], [$sql, $parameters]);

            // [PDO::FETCH_CLASS, 'person']
            if (is_array($fetch)) {
                return $statement->fetchAll(off($fetch, 0), off($fetch, 1));
            }
            return $statement->fetchAll($fetch);
        } catch (Throwable $error) {
            throw new SimplesPersistenceError([$sql, $parameters], [$error]);
        }
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
        try {
            $statement = $this->statement($sql);
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
        try {
            $statement = $this->statement($sql);
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
        try {
            $statement = $this->statement($instruction);
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
        try {
            $statement = $this->statement($instruction);
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
     * @throws SimplesRunTimeError
     */
    protected function filters(string $scope)
    {
        $getValue = function ($value) {
            return $value;
        };

        FilterMap::add($scope, 'equal', $getValue, runnable(static::class, 'equal'));

        FilterMap::add($scope, 'not', $getValue, runnable(static::class, 'not'));

        FilterMap::add($scope, 'blank', $getValue, runnable(static::class, 'blank'));

        FilterMap::add($scope, 'less-equal-than', $getValue, runnable(static::class, 'lessEqualThan'));

        FilterMap::add($scope, 'less-than', $getValue, runnable(static::class, 'lessThan'));

        FilterMap::add($scope, 'greater-than', $getValue, runnable(static::class, 'greaterThan'));

        FilterMap::add($scope, 'greater-equal-than', $getValue, runnable(static::class, 'greaterEqualThan'));

        $getPipedValue = function ($value) {
            return explode('|', $value);
        };

        FilterMap::add($scope, 'in', $getPipedValue, runnable(static::class, 'in'));
    }
}
