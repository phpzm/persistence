<?php

namespace Simples\Persistence\SQL\Operations;

/**
 * Class Destroy
 * @package Simples\Persistence\SQL\Operations
 */
trait Destroy
{
    /**
     * @param array $clausules
     * @return string
     */
    public function getDelete(array $clausules): string
    {
        $table = off($clausules, 'source', '[ source ]');
        $join = off($clausules, 'relation');

        $command = [];
        $command[] = 'DELETE FROM';
        $command[] = $table;
        if ($join) {
            $command[] = $this->parseJoin($join);
        }

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
