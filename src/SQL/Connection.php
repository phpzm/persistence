<?php

namespace Simples\Persistence\SQL;

use function is_callable;
use PDO;
use PDOStatement;
use Simples\Persistence\Connection as Persistence;

/**
 * Class Connection
 * @package Simples\Persistence
 */
abstract class Connection extends Persistence
{
    /**
     * @return PDO
     */
    protected function connection()
    {
        if (!$this->resource) {
            $user = $this->settings['user'];
            $password = $this->settings['password'];
            $options = $this->settings['options'];
            $this->resource = new PDO($this->dsn(), $user, $password, $options);
            $attributes = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true,
            ];
            foreach ($attributes as $key => $value) {
                $this->resource->setAttribute($key, $value);
            }
            if (isset($this->settings['bootstrap']) && is_callable($this->settings['bootstrap'])) {
                $this->settings['bootstrap']($this->resource);
            }
        }
        return $this->resource;
    }

    /**
     * @return string
     */
    abstract protected function dsn();

    /**
     * @param $sql
     * @return PDOStatement
     */
    final protected function statement($sql)
    {
        return $this->connection()->prepare($sql);
    }

    /**
     * @param $sql
     * @param array $values
     * @return int|null
     */
    final protected function execute($sql, array $values)
    {
        $statement = $this->statement($sql);

        if ($statement && $statement->execute(array_values($values))) {
            return $statement->rowCount();
        }

        return null;
    }
}
