<?php

namespace Simples\Persistence\SQL;

use PDO;
use Simples\Persistence\Error\SimplesPersistenceError;
use Throwable;
use Simples\Persistence\Driver as Persistence;
use Simples\Persistence\SQL\Error\SimplesPersistenceDataError;
use Simples\Persistence\SQL\Operations\Create;
use Simples\Persistence\SQL\Operations\Destroy;
use Simples\Persistence\SQL\Operations\Read;
use Simples\Persistence\SQL\Operations\Update;

/**
 * Class SQLDriver
 * @package Simples\Persistence
 */
abstract class Driver extends Connection implements Persistence
{
    /**
     * @trait Operations
     */
    Use Modifiers, Create, Read, Update, Destroy;

    /**
     * SQLDriver constructor.
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        parent::__construct($settings);
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
    final public function create(array $clausules, array $values)
    {
        $sql = $this->getInsert($clausules);
        $parameters = array_values($values);
        $this->addLog($sql, $parameters, off($clausules, 'log', false));
        $statement = $this->statement($sql);
        try {
            if ($statement && $statement->execute($parameters)) {
                return $this->connection()->lastInsertId();
            }
        } catch (Throwable $error) {
            throw new SimplesPersistenceError([$sql, $parameters], [$error]);
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
    final public function read(array $clausules, array $values = [])
    {
        $sql = $this->getSelect($clausules);
        $parameters = array_values($values);
        $this->addLog($sql, $parameters, off($clausules, 'log', false));
        $statement = $this->statement($sql);
        try {
            if ($statement && $statement->execute($parameters)) {
                return $statement->fetchAll(PDO::FETCH_ASSOC);
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
    final public function update(array $clausules, array $values, array $filters)
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
    final public function destroy(array $clausules, array $values)
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
}
