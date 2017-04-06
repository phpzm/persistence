<?php

namespace Simples\Persistence;

/**
 * Class Fusion
 * @package Simples\Persistence
 */
class Fusion
{
    /**
     * @var string
     */
    private $referenced;

    /**
     * @var string
     */
    private $collection;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $references;

    /**
     * @var bool
     */
    private $exclusive;

    /**
     * @var bool
     */
    private $rename;

    /**
     *
     * Fusion constructor.
     * @param string $collection
     * @param string $referenced
     * @param string $source
     * @param string $references
     * @param bool $exclusive
     * @param bool $rename
     */
    public function __construct(
        string $collection,
        string $referenced,
        string $source,
        string $references,
        $exclusive = false,
        $rename = true
    ) {
        $this->collection = $collection;
        $this->referenced = $referenced;
        $this->source = $source;
        $this->references = $references;
        $this->exclusive = $exclusive;
        $this->rename = $rename;
    }

    /**
     * @param string $collection
     * @param string $referenced
     * @param string $source
     * @param string $references
     * @param bool $exclusive
     * @param bool $rename
     * @return static
     */
    public static function create(
        string $collection,
        string $referenced,
        string $source,
        string $references,
        $exclusive = false,
        $rename = true
    ) {
        return new static($collection, $referenced, $source, $references, $exclusive, $rename);
    }

    /**
     * @return string
     */
    public function getReferenced(): string
    {
        return $this->referenced;
    }

    /**
     * @param string $referenced
     * @return Fusion
     */
    public function setReferenced(string $referenced): Fusion
    {
        $this->referenced = $referenced;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollection(): string
    {
        return $this->collection;
    }

    /**
     * @param string $collection
     * @return Fusion
     */
    public function setCollection(string $collection): Fusion
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * @return string
     */
    public function getReferences(): string
    {
        return $this->references;
    }

    /**
     * @param string $references
     * @return Fusion
     */
    public function setReferences(string $references): Fusion
    {
        $this->references = $references;
        return $this;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     * @return Fusion
     */
    public function setSource(string $source): Fusion
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExclusive(): bool
    {
        return $this->exclusive;
    }

    /**
     * @param bool $exclusive
     * @return Fusion
     */
    public function setExclusive(bool $exclusive): Fusion
    {
        $this->exclusive = $exclusive;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRename(): bool
    {
        return $this->rename;
    }

    /**
     * @param bool $rename
     * @return Fusion
     */
    public function setRename(bool $rename): Fusion
    {
        $this->rename = $rename;
        return $this;
    }
}
