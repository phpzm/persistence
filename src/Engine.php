<?php

namespace Simples\Persistence;

use Simples\Data\Error\SimplesValidationError;
use Simples\Error\SimplesRunTimeError;
use Simples\Kernel\App;

/**
 * Class Engine
 * @package Simples\Persistence
 *
 * @method $this source (string $source)
 * @method $this relation (array $relations)
 * @method $this fields (array | string $fields)
 * @method $this where (array | string $filter)
 * @method $this order (array | string $order)
 * @method $this group (array | string $group)
 * @method $this having (array | string $having)
 * @method $this limit (array | string $limit)
 * @method $this fetch (string $fetch)
 *
 * @method $this log (bool $active)
 */
abstract class Engine
{
    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var array
     */
    private $clauses = [];

    /**
     * @var array|mixed
     */
    private $settings = [];

    /**
     * Engine constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->settings = off(App::config('database'), $id);
        if ($this->settings) {
            $this->driver = Factory::create($this->settings);
        }
    }

    /**
     * Allow associate values to properties in clausules array
     *
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        $clausule = isset($arguments[0]) ? $arguments[0] : null;
        if (count($arguments) > 1) {
            $clausule = $arguments;
        }
        $name = strtolower($name);

        $this->clauses[$name] = $clausule;
        if (is_null($clausule)) {
            unset($this->clauses[$name]);
        }

        return $this;
    }

    /**
     * @return Driver
     * @throws SimplesRunTimeError
     */
    protected function driver(): Driver
    {
        if ($this->driver) {
            return $this->driver;
        }
        throw new SimplesRunTimeError("Cant use the driver", $this->settings);
    }

    /**
     * @param array $values
     * @param bool $reset
     * @return string
     * @throws SimplesRunTimeError
     */
    public function register($values, $reset = true): string
    {
        $clauses = $this->clauses;
        if ($reset) {
            $this->reset();
        }
        return $this->driver()->create($clauses, $values);
    }

    /**
     * @param $values
     * @param bool $reset
     * @return array
     * @throws SimplesRunTimeError
     */
    public function recover($values = [], $reset = true): array
    {
        $clauses = $this->clauses;
        if ($reset) {
            $this->reset();
        }
        return $this->driver()->read($clauses, $values);
    }

    /**
     * @param $values
     * @param $filters
     * @param bool $reset
     * @return int
     * @throws SimplesRunTimeError
     */
    public function change($values, $filters = [], $reset = true): int
    {
        $clauses = $this->clauses;
        if ($reset) {
            $this->reset();
        }
        return $this->driver()->update($clauses, $values, $filters);
    }

    /**
     * @param $filters
     * @param bool $reset
     * @return int
     * @throws SimplesRunTimeError
     */
    public function remove($filters, $reset = true): int
    {
        $clauses = $this->clauses;
        if ($reset) {
            $this->reset();
        }
        return $this->driver()->destroy($clauses, $filters);
    }

    /**
     * @return Driver
     */
    public function getDriver(): Driver
    {
        return $this->driver;
    }

    /**
     * @return array
     */
    public function getClauses(): array
    {
        return $this->clauses;
    }

    /**
     * @return array|mixed
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Clear the clausules changes
     */
    public function reset()
    {
        $this->clauses = [];
    }

    /**
     * @param string $name must to be a defined clause.
     * @param array $value
     * @return bool
     * @throws SimplesValidationError
     */
    public function merge(string $name, array $value)
    {
        if (isset($this->clauses[$name])) {
            $this->clauses[$name] = array_merge($this->clauses[$name], $value);
            return true;
        }
        throw new SimplesValidationError(
            [$name, $value],
            'This clause not defined yet, you can add something if this clause exist'
        );
    }

    /**
     * @param string $clausule
     * @return null
     */
    public function clause(string $clausule)
    {
        return $this->clauses[$clausule] ?: null;
    }
}
