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
 * @method $this fields (array|string $fields)
 * @method $this where (array $filter)
 * @method $this order (array $order)
 * @method $this group (array $group)
 * @method $this having (array $having)
 * @method $this limit (array $limit)
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
     * @return string
     */
    public function register($values): string
    {
        return $this->driver()->create($this->clauses, $values);
    }

    /**
     * @param $values
     * @return array
     */
    public function recover($values = []): array
    {
        return $this->driver()->read($this->clauses, $values);
    }

    /**
     * @param $values
     * @param $filters
     * @return int
     */
    public function change($values, $filters = []): int
    {
        return $this->driver()->update($this->clauses, $values, $filters);
    }

    /**
     * @param $filters
     * @return int
     */
    public function remove($filters): int
    {
        return $this->driver()->destroy($this->clauses, $filters);
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
