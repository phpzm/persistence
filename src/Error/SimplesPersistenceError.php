<?php

namespace Simples\Persistence\Error;

use Simples\Error\SimplesRunTimeError;

/**
 * Class PersistenceError
 * @package Simples\Persistence\Error
 */
class SimplesPersistenceError extends SimplesRunTimeError
{
    /**
     * @var int
     */
    protected $status = 412;

    /**
     * PersistenceError constructor.
     * @param array $details
     * @param array $context
     */
    public function __construct(array $details = [], array $context = [])
    {
        parent::__construct('Persistence error', $details, $context);
    }
}
