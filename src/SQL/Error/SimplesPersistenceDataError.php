<?php

namespace Simples\Persistence\SQL\Error;

use Simples\Persistence\Error\SimplesPersistenceError;

/**
 * Class PersistenceException
 * @package Simples\Error
 */
class SimplesPersistenceDataError extends SimplesPersistenceError
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param array $details
     * @return array
     */
    protected function parse(array $details): array
    {
        foreach ($details as $detail) {
            $this->parseDetail($detail);
        }
        return $this->errors;
    }

    /**
     * @param mixed $detail
     */
    private function parseDetail($detail)
    {
        switch (off($detail, 1)) {
            case 1452:
                $this->relationship(off($detail, 2));
                break;
            default:
                $this->errors[] = $detail;
        }
    }

    /**
     * @param $message
     */
    private function relationship($message)
    {
        preg_match('/FOREIGN KEY \(`(\w+)`\)/', $message, $matches);
        array_shift($matches);
        if (count($matches) === 1) {
            $field = $matches[0];
            $this->errors[$field] = ['relationship'];
            return;
        }
        $this->errors[] = $message;
    }
}
