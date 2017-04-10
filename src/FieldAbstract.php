<?php

namespace Simples\Persistence;

use Simples\Error\SimplesRunTimeError;
use stdClass;

/**
 * Class FieldAbstract
 * @package Simples\Persistence
 */
abstract class FieldAbstract
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $validators;

    /**
     * @var array
     */
    protected $enum;

    /**
     * @var array
     */
    protected $referenced;

    /**
     * @var stdClass
     */
    protected $references;

    /**
     * @var Field
     */
    protected $from;

    /**
     * @var callable
     */
    protected $calculated;

    /**
     * @var callable
     */
    protected $map;

    /**
     * @var array
     */
    protected $supported = ['string', 'text', 'datetime', 'date', 'integer', 'float', 'file', 'array', 'boolean'];

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws SimplesRunTimeError
     */
    public function __call($name, $arguments)
    {
        if (in_array($name, array_keys($this->options), true) && isset($arguments[0])) {
            $this->options[$name] = $arguments[0];
            return $this;
        }
        $parseName = $this->parseName($name);
        if ($parseName) {
            if (isset($arguments[0])) {
                $this->option($parseName, $arguments[0]);
                return $this;
            }
            return $this->option($parseName);
        }
        if (in_array($name, $this->supported, true)) {
            $this->option('type', $name);
            if (!$this->validators) {
                $this->optional();
            }
            return $this;
        }
        throw new SimplesRunTimeError("Type '{$name}' not supported");
    }

    /**
     * @param string $name
     * @return string
     */
    public function parseName(string $name): string
    {
        if (in_array(substr($name, 0, 3), ['get', 'set'])) {
            return lcfirst(substr($name, 3));
        }
        if (substr($name, 0, 2) === 'is') {
            return lcfirst(substr($name, 2));
        }
        return '';
    }

    /**
     * @param $record
     * @return mixed
     */
    public function calculate($record)
    {
        $callable = $this->calculated;
        return $callable($record);
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        if (is_callable($this->option('default'))) {
            $callable = $this->option('default');
            return $callable();
        }
        return $this->option('default');
    }

    /**
     * @return bool
     */
    public function isCalculated(): bool
    {
        return is_callable($this->calculated);
    }

    /**
     * @return bool
     */
    public function isMutable(): bool
    {
        return is_callable($this->option('mutator'));
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getEnum(): array
    {
        return $this->enum;
    }

    /**
     * @return array
     */
    public function getReferenced(): array
    {
        return $this->referenced;
    }

    /**
     * @return stdClass
     */
    public function getReferences(): stdClass
    {
        return $this->references;
    }

    /**
     * @return Field
     */
    public function getFrom(): Field
    {
        return $this->from;
    }

    /**
     * @return bool
     */
    public function hasFrom(): bool
    {
        return !!$this->from;
    }

    /**
     * @return callable
     */
    public function getMap(): callable
    {
        return $this->map;
    }

    /**
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }
}
