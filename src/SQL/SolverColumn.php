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

        switch ($column->getType()) {
            case Field::AGGREGATOR_COUNT:
                $field = "COUNT(`{$collection}`.`{$name}`)";
                /** @noinspection PhpAssignmentInConditionInspection */
                if ($alias = off($column->getOptions(), 'alias')) {
                    $field = "{$field} AS {$alias}";
                }
                break;
            default:
                $field = "`{$collection}`.`{$name}`";
        }
        return $field;
    }
}
