<?php

namespace Simples\Persistence\SQL;

use Simples\Persistence\Field;

/**
 * Class SolverColumn
 * @package Simples\Persistence\SQL
 */
class SolverColumn
{
    /**
     * @param string|Field $column
     * @return string
     */
    public function render($column): string
    {
        $field = '';
        if (gettype($column) === TYPE_STRING) {
            $field = "`{$column}`";
        }
        if ($column instanceof Field) {
            $field = $this->parseColumnField($column);
        }
        return $field;
    }

    /**
     * @param Field $column
     * @return string
     */
    private function parseColumnField(Field $column): string
    {
        $collection = $column->getCollection();
        if ($column->hasFrom()) {
            $collection = '__' . strtoupper($column->getFrom()->getName()) . '__';
        }
        $name = $column->getName();
        $alias = off($column->getOptions(), 'alias');

        $solvers = [
            Field::AGGREGATOR_COUNT => function ($collection, $name) {
                return "COUNT(`{$collection}`.`{$name}`)";
            },
            Field::AGGREGATOR_SUM => function ($collection, $name) {
                return "SUM(`{$collection}`.`{$name}`)";
            },
            Field::AGGREGATOR_MAX => function ($collection, $name) {
                return "MAX(`{$collection}`.`{$name}`)";
            },
            Field::AGGREGATOR_MIN => function ($collection, $name) {
                return "MIN(`{$collection}`.`{$name}`)";
            },
        ];
        $callable = function ($collection, $name) {
            return "`{$collection}`.`{$name}`";
        };
        if (isset($solvers[$column->getType()])) {
            $callable = $solvers[$column->getType()];
        }

        $field = $callable($collection, $name);
        if ($alias) {
            $field = "{$field} AS `{$alias}`";
        }
        return $field;
    }
}
