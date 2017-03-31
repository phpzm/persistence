<?php

namespace Simples\Persistence;

use Simples\Error\SimplesRunTimeError;
use Simples\Kernel\App;

/**
 * Class Engine
 * @package Simples\Persistence
 *
 * @method $this source (string $source)
 * @method $this relation (array $relations)
 * @method $this fields (array $fields)
 * @method $this where (array $filter)
 * @method $this order (array $order)
 * @method $this group (array $group)
 * @method $this having (array $having)
 * @method $this limit (array $limit)
 *
 * @method $this log (bool $active)
 */
class Engine
{
    /**
     * @var Driver
     */
    private $driver;

    /**
     * @var array
     */
    private $clausules = [];

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
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        $clausule = $arguments[0];
        if (count($arguments) > 1) {
            $clausule = $arguments;
        }
        $name = strtolower($name);

        $this->clausules[$name] = $clausule;
        if (is_null($clausule)) {
            unset($this->clausules[$name]);
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
    final public function register($values): string
    {
        return $this->driver()->create($this->clausules, $values);
    }

    /**
     * @param $values
     * @return array
     */
    final public function recover($values = []): array
    {
        return $this->driver()->read($this->clausules, $values);
    }

    /**
     * @param $values
     * @param $filters
     * @return int
     */
    final public function change($values, $filters = []): int
    {
        return $this->driver()->update($this->clausules, $values, $filters);
    }

    /**
     * @param $filters
     * @return int
     */
    final public function remove($filters): int
    {
        return $this->driver()->destroy($this->clausules, $filters);
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
    public function getClausules(): array
    {
        return $this->clausules;
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
        $this->clausules = [];
    }
}
