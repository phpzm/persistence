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
}
