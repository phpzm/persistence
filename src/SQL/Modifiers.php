<?php

namespace Simples\Persistence\SQL;

use function is_array;
use Simples\Error\SimplesRunTimeError;
use Simples\Persistence\Filter;
use Simples\Persistence\FilterMap;
use Simples\Persistence\Fusion;
use function is_string;

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
            if (!$value) {
                continue;
            }
            $key = ucfirst($key);
            $key = "parse{$key}";
            if (!method_exists($this, $key)) {
                throw new SimplesRunTimeError("Invalid modifier {$key}");
            }
            $value = $this->$key($value, $modifier['separator']);
            if (!$value) {
                continue;
            }
            $command[] = $modifier['instruction'] . ' ' . $value;
        }
        return $command;
    }

    /**
     * @param string $table
     * @param array|string $columns
     * @return string
     * @throws SimplesRunTimeError
     */
    protected function parseColumns($table, $columns): string
    {
        $type = gettype($columns);
        if ($type === TYPE_STRING) {
            return $columns;
        }
        if ($type === TYPE_ARRAY) {
            $solver = new SolverColumn();
            $fields = [];
            foreach ($columns as $column) {
                $fields[] = $solver->render($table, $column);
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
            if (is_string($resource)) {
                $join[] = $resource;
                continue;
            }
            $type = $resource->isExclusive() ? 'INNER' : 'LEFT';
            $collection = $resource->getCollection();
            $left = "`{$resource->getSource()}`.`{$resource->getReferences()}`";
            $alias = $resource->getCollection();
            if ($resource->isRename()) {
                $alias = $resource->alias();
            }
            $right = "`{$alias}`.`{$resource->getReferenced()}`";
            $join[] = "{$type} JOIN `{$collection}` AS {$alias} ON ({$left} = {$right})";
        }

        return implode(' ', $join);
    }

    /**
     * @param array|string $filters
     * @param string $separator
     * @return string
     * @throws SimplesRunTimeError
     */
    protected function parseWhere($filters, string $separator): string
    {
        $parsed = [];
        if (!is_array($filters)) {
            $filters = [$filters];
        }
        foreach ($filters as $filter) {
            if (is_array($filter)) {
                $parsing = $this->parseWhere($filter['filter'], $filter['separator']);
                $parsed[] = "({$parsing})";
                continue;
            }
            if (gettype($filter) === TYPE_STRING) {
                $parsed[] = $filter;
                continue;
            }
            /** @var Filter $filter */
            $rule = $filter->getRule();
            if (!FilterMap::has($this->scope(), $rule)) {
                throw new SimplesRunTimeError("SQLFilterSolver can't resolve '{$rule}'");
            }
            $collection = $filter->getCollection();
            if ($filter->hasFrom()) {
                $collection = Fusion::relation($filter->getFrom()->getName());
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
     * @param array|string $groups
     * @param string $separator
     * @return string
     */
    protected function parseGroup($groups, string $separator): string
    {
        if (!is_array($groups)) {
            $groups = [$groups];
        }
        return implode($separator, $groups);
    }

    /**
     * @param array|string $orders
     * @param string $separator
     * @return string
     */
    protected function parseOrder($orders, string $separator): string
    {
        if (!is_array($orders)) {
            $orders = [$orders];
        }
        return implode($separator, $orders);
    }

    /**
     * @param array|string $having
     * @param string $separator
     * @return string
     */
    protected function parseHaving($having, string $separator): string
    {
        if (!is_array($having)) {
            $having = [$having];
        }
        return implode($separator, $having);
    }

    /**
     * @param array|string $limits
     * @param string $separator
     * @return string
     */
    protected function parseLimit($limits, string $separator): string
    {
        if (!is_array($limits)) {
            $limits = [$limits];
        }
        return implode($separator, $limits);
    }
}
