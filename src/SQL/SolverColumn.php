<?php

namespace Simples\Persistence\SQL;

use Simples\Persistence\Field;
use Simples\Persistence\Fusion;
use Simples\Persistence\SQL\Error\SimplesUnsupportedField;

/**
 * Class SolverColumn
 * @package Simples\Persistence\SQL
 */
class SolverColumn
{
    /**
     * @param string $table
     * @param string|Field $column
     * @return string
     * @throws SimplesUnsupportedField
     */
    public function render($table, $column): string
    {
        if (gettype($column) === TYPE_STRING) {
            return "`{$table}`.`{$column}`";
        }
        if ($column instanceof Field) {
            return $this->parseColumnField($column);
        }
        if (gettype($column) === TYPE_ARRAY) {
            $source = (string)off($column, 0);
            $field = (string)off($column, 1);
            $as = (string)off($column, 2);
            if ($source && $field && $as) {
                return "`{$source}`.`{$field}` AS `{$as}`";
            }
            if ($source && $field) {
                return "`{$source}`.`{$field}`";
            }
            if ($field && $as) {
                return "{$field} AS `{$as}`";
            }
        }
        throw new SimplesUnsupportedField(['table' => $table, 'column' => $column]);
    }

    /**
     * @param Field $column
     * @return string
     */
    private function parseColumnField(Field $column): string
    {
        $collection = $column->getCollection();
        if ($column->hasFrom()) {
            $collection = Fusion::relation($column->getFrom()->getName());
        }
        $name = $column->getName();
        $options = $column->getOptions();
        $alias = off($options, 'alias');

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
        if (isset($options['expression']) && $options['expression']) {
            $alias = $name;
            $callable = function () use ($options) {
                return "({$options['expression']})";
            };
        }

        $field = $callable($collection, $name);
        if ($alias) {
            $field = "{$field} AS `{$alias}`";
        }
        return $field;
    }
}
