<?php

namespace Simples\Persistence\SQL\Error;

use Simples\Persistence\Error\SimplesPersistenceError;

/**
 * Class SimplesUnsupportedField
 * @package Simples\Persistence\SQL\Error
 */
class SimplesUnsupportedField extends SimplesPersistenceError
{
    protected $status = 500;
}