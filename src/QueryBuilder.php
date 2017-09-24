<?php

namespace Simples\Persistence;

use Simples\Error\SimplesRunTimeError;
use Simples\Kernel\Container;

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
     * @return $this
     * @throws SimplesRunTimeError
     */
    public static function instance()
    {
        return Container::instance()->make(static::class);
    }

    /**
     * @return bool
     * @throws SimplesRunTimeError
     */
    public function commit()
    {
        return $this->driver()->commit();
    }

    /**
     * @return bool
     * @throws SimplesRunTimeError
     */
    public function rollback()
    {
        return $this->driver()->rollback();
    }

    /**
     * @param string $sql
     * @param array $values
     * @return mixed
     * @throws SimplesRunTimeError
     */
    public function run(string $sql, array $values)
    {
        return $this->driver()->run($sql, $values);
    }

    /**
     * @param string $sql
     * @param array $values
     * @return mixed
     * @throws SimplesRunTimeError
     */
    public function query(string $sql, array $values)
    {
        return $this->driver()->query($sql, $values);
    }
}
