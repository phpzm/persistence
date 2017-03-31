<?php

namespace Simples\Persistence\SQL\Operations;

/**
 * Class Create
 * @package Simples\Persistence\SQL\Operations
 */
trait Create
{
    /**
     * @param array $clausules
     * @return string
     */
    public function getInsert(array $clausules): string
    {
        $source = off($clausules, 'source', '[ source ]');
        $fields = off($clausules, 'fields', '[ fields ]');

        $inserts = array_slice(explode(',', str_repeat(',?', count($fields))), 1);

        $command = [];
        $command[] = 'INSERT INTO';
        $command[] = $source;
        $command[] = '(' . (is_array($fields) ? implode(__COMMA__, $fields) : $fields) . ')';
        $command[] = 'VALUES';
        $command[] = '(' . implode(__COMMA__, $inserts) . ')';

        return implode(' ', $command);
    }
}
