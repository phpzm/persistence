<?php

namespace Simples\Persistence\Utils;

/**
 * Class Naming
 * @package Simples\Persistence\Utils
 */
trait Naming
{
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
}
