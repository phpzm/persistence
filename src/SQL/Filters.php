<?php

namespace Simples\Persistence\SQL;

/**
 * Trait Filters
 * @package Simples\Persistence\SQL
 */
trait Filters
{
    /**
     * @param string $name
     * @return string
     */
    public static function equal(string $name)
    {
        return "{$name} = ?";
    }

    /**
     * @param string $name
     * @return string
     */
    public static function not(string $name)
    {
        return "{$name} <> ?";
    }

    /**
     * @param string $name
     * @return string
     */
    public static function lessEqualThan(string $name)
    {
        return "{$name} <= ?";
    }

    /**
     * @param string $name
     * @return string
     */
    public static function lessThan(string $name)
    {
        return "{$name} < ?";
    }

    /**
     * @param string $name
     * @return string
     */
    public static function greaterEqualThan(string $name)
    {
        return "{$name} >= ?";
    }

    /**
     * @param string $name
     * @return string
     */
    public static function greaterThan(string $name)
    {
        return "{$name} > ?";
    }

    /**
     * @param string $name
     * @return string
     */
    public static function blank(string $name)
    {
        return "({$name} IS NULL) OR (NOT {$name})";
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    public static function in(string $name, string $value)
    {
        $callback = function () {
            return '?';
        };
        $array = explode('|', $value);
        $pieces = array_map($callback, $array);
        $keys = implode(',', $pieces);
        return "{$name} IN ({$keys})";
    }
}
