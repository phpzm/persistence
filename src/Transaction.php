<?php

namespace Simples\Persistence;

/**
 * Class Transaction
 * @package Simples\Persistence
 */
class Transaction
{
    /**
     * @var array
     */
    private static $connections = [];

    /**
     * @param $driver
     * @return Driver|null
     */
    public static function recover($driver)
    {
        return isset(self::$connections[$driver]) ? self::$connections[$driver] : null;
    }

    /**
     * @param $driver
     * @param Driver $connection
     * @return mixed
     */
    public static function register($driver, Driver $connection)
    {
        $connection->start();

        self::$connections[$driver] = $connection;

        return self::$connections[$driver];
    }

    /**
     * @return bool
     */
    public static function commit()
    {
        foreach (self::$connections as $connection) {
            /** @var Driver $connection */
            if (!$connection->commit()) {
                return self::rollback();
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public static function rollback()
    {
        $rollback = true;
        foreach (self::$connections as $connection) {
            /** @var Driver $connection */
            if (!$connection->rollback()) {
                $rollback = false;
            }
        }
        return $rollback;
    }
}
