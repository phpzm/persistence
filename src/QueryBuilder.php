<?php

namespace Simples\Persistence;

/**
 * Class QueryBuilder
 * @package Simples\Persistence
 */
class QueryBuilder extends Engine
{
    /**
     * Defines which connection will be used
     * @var string
     */
    protected $connection = 'default';

    /**
     * QueryBuilder constructor.
     */
    public function __construct()
    {
        parent::__construct($this->connection);
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->driver()->commit();
    }

    /**
     * @return bool
     */
    public function rollback()
    {
        return $this->driver()->rollback();
    }

    /**
     * @param string $sql
     * @param array $values
     * @return mixed
     */
    public function run(string $sql, array $values)
    {
        return $this->driver()->run($sql, $values);
    }

    /**
     * @param string $sql
     * @param array $values
     * @return mixed
     */
    public function query(string $sql, array $values)
    {
        return $this->driver()->query($sql, $values);
    }
}
