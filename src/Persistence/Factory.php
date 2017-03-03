<?php

namespace Simples\Persistence;

/**
 * Class Factory
 * @package Simples\Persistence
 */
class Factory
{
    /**
     * @param $settings
     * @return Driver
     */
    public static function create($settings)
    {
        $driver = $settings['driver'];
        if (class_exists($driver)) {
            $connection = Transaction::recover($driver);
            if ($connection) {
                return $connection;
            }
            return Transaction::register($driver, new $driver($settings));
        }
        return null;
    }
}
