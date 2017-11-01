<?php

namespace Simples\Persistence\Drivers;

use Simples\Error\SimplesRunTimeError;
use Simples\Helper\JSON;
use Simples\Persistence\FilterMap;
use Simples\Persistence\SQL\Driver;

/**
 * Class MySQL
 * @package Simples\Persistence
 */
class MySQL extends Driver
{
    /**
     * @var string
     */
    protected $scope = 'mysql';

    /**
     * @return string
     */
    protected function dsn()
    {
        $host = "host={$this->settings['host']}";
        $port = "port={$this->settings['port']}";
        $dbname = "dbname={$this->settings['database']}";

        return "{$this->scope}:{$host};{$port};{$dbname}";
    }

    /**
     * @return string
     */
    public function scope(): string
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     */
    protected function filters(string $scope)
    {
        $getValue = function ($value) {
            return $value;
        };

        FilterMap::add(
            $scope,
            'like',
            function ($value) {
                if (!is_scalar($value)) {
                    $value = JSON::encode($value);
                }
                return "%{$value}%";
            },
            function ($name) {
                return "{$name} LIKE ?";
            }
        );

        FilterMap::add(
            $scope,
            'between',
            function ($value) {
                $separator = ',';
                $size = 2;
                return $this->separator('between', $value, $separator, $size);
            },
            function ($name) {
                return "{$name} BETWEEN ? AND ?";
            }
        );

        FilterMap::add($scope, 'day', $getValue, function ($name) {
            return "DAY({$name}) = ?";
        });

        FilterMap::add($scope, 'month', $getValue, function ($name) {
            return "MONTH({$name}) = ?";
        });

        FilterMap::add($scope, 'year', $getValue, function ($name) {
            return "YEAR({$name}) = ?";
        });

        parent::filters($scope);
    }

    /**
     * @param string $rule
     * @param string $value
     * @param string $separator
     * @param int $size
     * @return array
     * @throws SimplesRunTimeError
     */
    public function separator(string $rule, string $value, string $separator, int $size): array
    {
        $array = explode($separator, $value);
        if (count($array) < $size) {
            $count = count($array);
            throw new SimplesRunTimeError("Invalid number of arguments to create a rule. " .
                "Expected '{$size}' given '{$count}' to rule '{$rule}'");
        }
        if (count($array) > $size) {
            $array = array_slice($array, 0, $size);
        }
        return $array;
    }
}
