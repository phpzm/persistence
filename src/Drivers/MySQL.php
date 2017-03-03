<?php

namespace Simples\Persistence\Drivers;

use Simples\Persistence\SQL\SQLDriver;

/**
 * Class MySQL
 * @package Simples\Persistence
 */
class MySQL extends SQLDriver
{
    /**
     * @return string
     */
    protected function dsn()
    {
        $host = "host={$this->settings['host']}";
        $port = "port={$this->settings['port']}";
        $dbname = "dbname={$this->settings['database']}";

        return "mysql:{$host};{$port};{$dbname}";
    }
}
