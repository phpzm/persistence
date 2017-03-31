<?php

namespace Simples\Persistence\SQL;

use Simples\Error\SimplesRunTimeError;
use Simples\Kernel\Wrapper;
use Simples\Persistence\Filter;
use Simples\Persistence\FilterMap;
use Simples\Persistence\Fusion;

/**
 * Class Modifiers
 * @package Simples\Persistence\SQL
 */
trait Modifiers
{
    /**
     * @param array $clausules
     * @param array $modifiers
     * @return array
     * @throws SimplesRunTimeError
     * @see parseColumns
     * @see parseJoin
     * @see parseWhere
     * @see parseGroup
     * @see parseOrder
     * @see parseLimit
     */
    protected function modifiers(array $clausules, array $modifiers): array
    {
        $command = [];
        foreach ($modifiers as $key => $modifier) {
            $value = off($clausules, $key);
            if ($value) {
                $key = ucfirst($key);
                $key = "parse{$key}";
                if (!method_exists($this, $key)) {
                    throw new SimplesRunTimeError("Invalid modifier {$key}");
                }
                $value = $this->$key($value, $modifier['separator']);
                $command[] = $modifier['instruction'] . ' ' . $value;
            }
        }
        return $command;
    }

    /**
     * @param $columns
     * @return string
     * @throws SimplesRunTimeError
     */
    protected function parseColumns($columns): string
    {
        $type = gettype($columns);
        if ($type === TYPE_STRING) {
            return $columns;
        }
        if ($type === TYPE_ARRAY) {
            $solver = new SolverColumn();
            $fields = [];
            foreach ($columns as $column) {
                $fields[] = $solver->render($column);
            }
            return implode(__COMMA__, $fields);
        }
        throw new SimplesRunTimeError("Columns must be an 'array' or 'string', {$type} given");
    }

    /**
     * @param array $resources
     * @return string
     */
    protected function parseJoin(array $resources): string
    {
        $join = [];
        /** @var Fusion $resource */
        foreach ($resources as $resource) {
            $type = $resource->isExclusive() ? 'INNER' : 'LEFT';
            $collection = $resource->getCollection();
            $left = "`{$resource->getSource()}`.`{$resource->getReferences()}`";
            $alias = $resource->getCollection();
            if ($resource->isRename()) {
                $alias = '__' . strtoupper($resource->getReferences()) . '__';
            }
            $right = "`{$alias}`.`{$resource->getReferenced()}`";
            $join[] = "{$type} JOIN `{$collection}` AS {$alias} ON ({$left} = {$right})";
        }

        return implode(' ', $join);
    }

    /**
     * @param array $filters
     * @param string $separator
     * @return string
     * @throws SimplesRunTimeError
     */
    protected function parseWhere(array $filters, string $separator): string
    {
        $parsed = [];
        foreach ($filters as $filter) {
            if (is_array($filter)) {
                $parsing = $this->parseWhere($filter['filter'], $filter['separator']);
                $parsed[] = "({$parsing})";
                continue;
            }
            /** @var Filter $filter */
            $rule = $filter->getRule();
            if (!FilterMap::has($this->scope(), $rule)) {
                throw new SimplesRunTimeError("SQLFilterSolver can't resolve '{$rule}'");
            }
            $collection = $filter->getCollection();
            if ($filter->hasFrom()) {
                $collection = '__' . strtoupper($filter->getFrom()->getName()) . '__';
            }
            $name = "{$collection}.{$filter->getName()}";
            $value = $filter->getValue();
            $not = $filter->isNot() ? 'NOT ' : '';
            /** @var Driver $this */
            $parsed[] = "{$not}(" . FilterMap::parseMarkup($this, $rule, $name, $value) . ")";
        }
        return implode($separator, $parsed);
    }

    /**
     * @param array $groups
     * @param string $separator
     * @return string
     */
    protected function parseGroup(array $groups, string $separator): string
    {
        return implode($separator, $groups);
    }

    /**
     * @param array $orders
     * @param string $separator
     * @return string
     */
    protected function parseOrder(array $orders, string $separator): string
    {
        return implode($separator, $orders);
    }

    /**
     * @param array $having
     * @param string $separator
     * @return string
     */
    protected function parseHaving(array $having, string $separator): string
    {
        return implode($separator, $having);
    }

    /**
     * @param array $limits
     * @param string $separator
     * @return string
     */
    protected function parseLimit(array $limits, string $separator): string
    {
        return implode($separator, $limits);
    }
}
