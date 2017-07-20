<?php

namespace Simples\Persistence\SQL\Operations;

/**
 * Class Read
 * @package Simples\Persistence\SQL\Operations
 */
trait Read
{
    /**
     * @param array $clausules
     * @return string
     */
    public function getSelect(array $clausules): string
    {
        $table = off($clausules, 'source', '[ source ]');
        $columns = off($clausules, 'fields', '[ fields ]');
        $join = off($clausules, 'relation');

        $command = [];
        $command[] = 'SELECT';
        $command[] = $this->parseColumns($table, $columns);
        $command[] = 'FROM';
        $command[] = $table;
        if ($join) {
            $command[] = $this->parseJoin($join);
        }

        $modifiers = [
            'where' => [
                'instruction' => 'WHERE',
                'separator' => __AND__,
            ],
            'group' => [
                'instruction' => 'GROUP BY',
                'separator' => __COMMA__,
            ],
            'order' => [
                'instruction' => 'ORDER BY',
                'separator' => __COMMA__,
            ],
            'having' => [
                'instruction' => 'HAVING',
                'separator' => __AND__,
            ],
            'limit' => [
                'instruction' => 'LIMIT',
                'separator' => __COMMA__,
            ],
        ];
        $command = array_merge($command, $this->modifiers($clausules, $modifiers));

        return implode(' ', $command);
    }
}
