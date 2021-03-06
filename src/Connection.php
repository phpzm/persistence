<?php

namespace Simples\Persistence;

use Simples\Kernel\App;
use Simples\Kernel\Wrapper;

/**
 * Class Connection
 * @package Simples\Persistence
 */
abstract class Connection
{
    /**
     * @var mixed
     */
    protected $resource = null;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var array
     */
    protected $logs = [];

    /**
     * Connection constructor.
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return mixed
     */
    abstract protected function connection();

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @return array
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     *
     * @param string $command
     * @param array $parameters
     * @param bool $logging (false)
     * @return Connection
     */
    public function addLog(string $command, array $parameters, bool $logging = false): Connection
    {
        $log = ['command' => $command, 'parameters' => $parameters];
        $this->logs[] = $log;
        if ($logging || App::logging()) {
            Wrapper::log($log);
        }
        return $this;
    }
}
