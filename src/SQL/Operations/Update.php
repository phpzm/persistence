<?php

namespace Simples\Persistence\SQL\Operations;

/**
 * Class Update
 * @package Simples\Persistence\SQL\Operations
 */
trait Update
{
    /**
     * @param array $clausules
     * @return string
     */
    public function getUpdate(array $clausules): string
    {
        $table = off($clausules, 'source', '[ source ]');
        $join = off($clausules, 'relation');
        $columns = off($clausules, 'fields', '[ fields ]');

        $sets = $columns;
        if (is_array($columns)) {
            $fields = [];
            foreach ($columns as $field) {
                $fields[] = "{$field} = ?";
            }
            $sets = implode(__COMMA__, $fields);
        }

        $command = [];
        $command[] = 'UPDATE';
        $command[] = $table;
        if ($join) {
            $command[] = $this->parseJoin($join);
        }
        $command[] = 'SET';
        $command[] = $sets;

        $modifiers = [
            'where' => [
                'instruction' => 'WHERE',
                'separator' => __AND__,
            ]
        ];
        $command = array_merge($command, $this->modifiers($clausules, $modifiers));

        return implode(' ', $command);
    }
}
