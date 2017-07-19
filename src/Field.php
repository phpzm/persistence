<?php

namespace Simples\Persistence;

use Simples\Error\SimplesRunTimeError;

/**
 * @method Field string(int $size = 255)
 * @method Field text()
 * @method Field datetime(string $format = 'Y-m-d H:i:s')
 * @method Field date(string $format = 'Y-m-d')
 * @method Field integer(int $size = 10)
 * @method Field float(int $size = 10, int $decimal = 4)
 * @method Field file()
 * @method Field array()
 * @method Field boolean()
 *
 * @method Field collection(string $collection)
 * @method Field name(string $name)
 * @method Field type(string $type)
 * @method Field label(string $label)
 * @method Field alias(string $alias)
 * @method Field create(bool $create)
 * @method Field read(bool $read)
 * @method Field update(bool $update)
 * @method Field recover(bool $recover)
 * @method Field mutator(callable $mutation)
 *
 * @method string getCollection()
 * @method Field setCollection(string $collection)
 * @method string getName()
 * @method Field setName(string $name)
 * @method string getType()
 * @method Field setType(string $type)
 * @method string getLabel()
 * @method Field setLabel(string $label)
 * @method string getAlias()
 * @method Field setAlias(string $alias)
 * @method bool isPrimaryKey()
 * @method Field setPrimaryKey(bool $primaryKey)
 * @method bool isCreate()
 * @method Field setCreate(bool $create)
 * @method bool isRead()
 * @method Field setRead(bool $read)
 * @method bool isUpdate()
 * @method Field setUpdate(bool $update)
 * @method bool isRecover()
 * @method Field setRecover(bool $recover)
 *
 * @method Field default(mixed $default)
 *
 * Class Field
 * @package Simples\Model
 */
class Field extends FieldAbstract
{
    /**
     * @var string
     */
    const TYPE_STRING = 'string', TYPE_DATETIME = 'datetime', TYPE_BOOLEAN = 'boolean',
        TYPE_DATE = 'date', TYPE_INTEGER = 'integer', TYPE_FLOAT = 'float', TYPE_TEXT = 'text', TYPE_FILE = 'file',
        TYPE_ARRAY = 'array';

    /**
     * @var string
     */
    const AGGREGATOR_COUNT = 'count', AGGREGATOR_SUM = 'sum', AGGREGATOR_MAX = 'max', AGGREGATOR_MIN = 'min';

    /**
     * Field constructor.
     * @param string $collection
     * @param string $name
     * @param string $type (null)
     * @param array $options ([])
     */
    public function __construct(string $collection, string $name, string $type = null, array $options = [])
    {
        $default = [
            'collection' => $collection,
            'name' => $name,
            'type' => $type ?? Field::TYPE_STRING,
            'primaryKey' => false,
            'label' => '',
            'default' => '',
            'alias' => '',
            'mutator' => null,
            'create' => true,
            'read' => true,
            'update' => true,
            'recover' => true
        ];
        $this->options = array_merge($default, $options);

        if (off($this->options, 'primaryKey')) {
            $this->create(false);
            $this->update(false);
        }
        if (!is_array($this->validators)) {
            $this->optional();
        }

        $this->validators = [];
        $this->enum = [];
        $this->referenced = [];
        $this->references = (object)[];
    }

    /**
     * @param string $collection
     * @param string $name
     * @param string $type (null)
     * @param array $options ([])
     * @return Field
     */
    public static function make(string $collection, string $name, string $type = null, array $options = []): Field
    {
        return new static($collection, $name, $type, $options);
    }

    /**
     * @param string $key
     * @param mixed $value (null)
     * @return mixed
     */
    public function option(string $key, $value = null)
    {
        if ($value) {
            $this->options[$key] = $value;
        }
        return off($this->options, $key);
    }

    /**
     * @param string $class
     * @param string $target
     * @param string $name (null)
     * @return Field
     */
    public function referencedBy(string $class, string $target, string $name = null): Field
    {
        $pivot = !is_null($name);
        if (!$name) {
            $name = get_class_short_name($class);
        }
        $this->referenced[$target] = [
            'pivot' => $pivot,
            'name' => $name,
            'class' => $class
        ];
        return $this;
    }

    /**
     *
     * @param string $class
     * @param string $referenced
     * @param bool $nullable (false)
     * @param string $name (null)
     * @param bool $fusion (true)
     * @return Field
     * @throws SimplesRunTimeError
     */
    public function referencesTo(
        string $class,
        string $referenced,
        bool $nullable = false,
        string $name = null,
        bool $fusion = true
    ): Field {
        if (off($this->references, 'class')) {
            throw new SimplesRunTimeError("Relationship already defined to '{$this->references->class}'");
        }
        if (!$name) {
            $name = get_class_short_name($class);
        }
        $this->references = (object)[
            'name' => $name,
            'collection' => $this->getCollection(),
            'referenced' => $referenced,
            'class' => $class,
            'fusion' => $fusion
        ];
        if ($nullable) {
            $this->option('default', null);
        }
        return $this;
    }

    /**
     * @param array $items
     * @return Field
     */
    public function enum(array $items): Field
    {
        if (!$this->option('type')) {
            $this->string();
        }
        $this->enum = $items;
        return $this;
    }

    /**
     * @return Field
     */
    public function nullable(): Field
    {
        $this->option('default', null);
        return $this;
    }

    /**
     * @param callable $callable
     * @return Field
     */
    public function calculated(callable $callable): Field
    {
        $this->calculated = $callable;
        return $this;
    }

    /**
     *
     * @param array $rules
     * @return Field
     * @internal param bool $force
     */
    public function required(array $rules = []): Field
    {
        $this->validator(['required', $this->option('type')], null, true);
        foreach ($rules as $rule) {
            $this->validator($rule, ['optional' => true], false);
        }
        return $this;
    }

    /**
     * @param array $rules
     * @return Field
     */
    public function optional(array $rules = []): Field
    {
        $this->validator($this->option('type'), ['optional' => true], true);
        foreach ($rules as $rule) {
            $this->validator($rule, ['optional' => true], false);
        }
        return $this;
    }

    /**
     * @param Field $reference
     * @return Field
     */
    public function from(Field $reference): Field
    {
        $this->from = $reference;
        return $this->create(false)->read(true)->update(false);
    }

    /**
     * @return Field
     */
    public function readonly(): Field
    {
        $this->option('readonly', true);
        return $this->create(false)->read(false)->update(false);
    }

    /**
     * @return Field
     */
    public function reject(): Field
    {
        $this->validators = ['reject' => ''];
        $this->enum = [];
        return $this;
    }

    /**
     * @return Field
     */
    public function primaryKey(): Field
    {
        $this->option('primaryKey', true);
        return $this->integer()->create(false)->update(false);
    }

    /**
     * @return Field
     */
    public function hashKey(): Field
    {
        return $this->string()->optional(['unique'])->update(false);
    }

    /**
     *
     * @param string|array $rule
     * @param array|string $options ('')
     * @param bool $clear
     * @return Field
     */
    public function validator($rule, $options = null, bool $clear = false): Field
    {
        if ($clear) {
            $this->validators = [];
        }
        if (!is_array($rule)) {
            $this->validators[$rule] = $options;
            return $this;
        }
        foreach ($rule as $key => $value) {
            $name = $key;
            if (is_numeric($key)) {
                $name = $value;
                $value = '';
            }
            $this->validators[$name] = $value;
        }
        return $this;
    }
}
