<?php

namespace Simples\Persistence;

/**
 * Class FilterMap
 * @package Simples\Persistence
 */
class FilterMap
{
    /**
     * @var array
     */
    private static $rules = [];

    /**
     * @param string $scope
     * @param string $rule
     * @param callable $value
     * @param callable $markup
     * @return array
     */
    public static function add(string $scope, string $rule, callable $value, callable $markup)
    {
        if (!isset(static::$rules[$scope])) {
            static::$rules[$scope] = [];
        }
        static::$rules[$scope][$rule] = [
            'getMarkup' => $markup,
            'getValue' => $value
        ];
        return static::$rules;
    }

    /**
     * @param string $scope
     * @param string $rule
     * @return bool
     */
    public static function has(string $scope, string $rule): bool
    {
        if (!isset(static::$rules[$scope])) {
            return false;
        }
        return isset(static::$rules[$scope][$rule]);
    }

    /**
     * @param string $scope
     * @param string $rule
     * @return mixed
     */
    public static function get(string $scope, string $rule)
    {
        if (static::has($scope, $rule)) {
            return static::$rules[$scope][$rule];
        }
        return null;
    }

    /**
     * @param string $scope
     * @param string $rule
     * @return mixed
     */
    public static function getValue(string $scope, string $rule)
    {
        if (static::has($scope, $rule)) {
            return static::$rules[$scope][$rule]['getValue'];
        }
        return null;
    }

    /**
     * @param string $scope
     * @param string $rule
     * @return callable
     */
    public static function getMarkup(string $scope, string $rule): callable
    {
        if (static::has($scope, $rule)) {
            return static::$rules[$scope][$rule]['getMarkup'];
        }
        return null;
    }

    /**
     * @param Driver $driver
     * @param string $rule
     * @param $value
     * @return mixed
     */
    public static function parseValue(Driver $driver, string $rule, $value)
    {
        $scope = $driver->scope();
        if (!isset(static::$rules[$scope])) {
            return $value;
        }
        if (isset(static::$rules[$scope][$rule])) {
            $getValue = static::$rules[$scope][$rule]['getValue'];
            return $getValue($value, $driver);
        }
        return $value;
    }

    /**
     * @param Driver $driver
     * @param string $rule
     * @param string $name
     * @param $value
     * @return mixed
     */
    public static function parseMarkup(Driver $driver, string $rule, string $name, $value)
    {
        $scope = $driver->scope();
        if (!isset(static::$rules[$scope])) {
            return $value;
        }
        if (isset(static::$rules[$scope][$rule])) {
            $getMarkup = static::$rules[$scope][$rule]['getMarkup'];
            return $getMarkup($name, $value, $driver);
        }
        return $value;
    }
}
