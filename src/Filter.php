<?php

namespace Simples\Persistence;

use Simples\Helper\JSON;
use Simples\Kernel\App;

/**
 * Class Filter
 * @package Simples\Persistence
 */
class Filter
{
    /**
     * @var Field
     */
    private $field;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $rule;

    /**
     * @var bool
     */
    private $not;

    /**
     * @var string
     */
    const RULE_EQUAL = 'equal';
    /**
     * @var string
     */
    const RULE_NOT = 'not';
    /**
     * @var string
     */
    const RULE_LIKE = 'like';
    /**
     * @var string
     */
    const RULE_BETWEEN = 'between';
    /**
     * @var string
     */
    const RULE_DAY = 'day';
    /**
     * @var string
     */
    const RULE_MONTH = 'month';
    /**
     * @var string
     */
    const RULE_YEAR = 'year';
    /**
     * @var string
     */
    const RULE_BLANK = 'blank';

    /**
     * Filter constructor.
     *
     * @param Field $field
     * @param mixed $value
     * @param string $rule (null)
     * @param bool $not (false)
     */
    public function __construct(Field $field, $value, $rule = null, $not = false)
    {
        $this->field = $field;
        $this->value = $value;
        $this->not = $not;
        $this->parseRule($rule);
    }

    /**
     * @param Field $field
     * @param $value
     * @param null $rule
     * @param bool $not
     * @return static
     */
    public static function create(Field $field, $value, $rule = null, $not = false)
    {
        return new static($field, $value, $rule, $not);
    }

    /**
     * @param $rule
     */
    private function parseRule($rule)
    {
        $this->rule = $rule;
        if (!$rule) {
            $this->rule = static::RULE_EQUAL;
            $peaces = explode(App::options('filter'), $this->value);
            $filter = (string)$peaces[0];
            if (substr($filter, 0, 1) === '!') {
                $filter = substr($filter, 1);
                $this->not = true;
            }
            if (isset($peaces[1])) {
                $this->rule = $filter;
                array_shift($peaces);
                $this->value = implode(App::options('filter'), $peaces);
            }
        }
    }

    /**
     * @param string $rule
     * @param mixed $value
     * @return string
     */
    public static function apply(string $rule, $value = null): string
    {
        $marker = App::options('filter');
        if (!is_scalar($value)) {
            $value = JSON::encode($value);
        }
        return "{$rule}{$marker}{$value}";
    }

    /**
     * @param array $filter
     * @param string $separator
     * @return array
     */
    public static function generate(array $filter, string $separator = __AND__): array
    {
        return [
            'separator' => $separator,
            'filter' => $filter
        ];
    }

    /**
     * @param Driver $driver
     * @return mixed
     */
    public function getParsedValue(Driver $driver)
    {
        return FilterMap::parseValue($driver, $this->rule, $this->value);
    }

    /**
     * @return string
     */
    public function getCollection(): string
    {
        return $this->field->getCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->field->getName();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->field->getType();
    }

    /**
     * @return bool
     */
    public function hasFrom(): bool
    {
        return $this->field->hasFrom();
    }

    /**
     * @return Field
     */
    public function getFrom(): Field
    {
        return $this->field->getFrom();
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getRule(): string
    {
        return $this->rule;
    }

    /**
     * @return bool
     */
    public function isNot(): bool
    {
        return $this->not;
    }
}
