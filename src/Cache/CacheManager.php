<?php

namespace Ycs77\LaravelWizard\Cache;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Support\Manager;
use Ycs77\LaravelWizard\Contracts\Cache;
use Ycs77\LaravelWizard\Wizard;

class CacheManager extends Manager
{
    /** @var Wizard */
    protected $wizard;

    public function __construct(Container $container, Wizard $wizard)
    {
        parent::__construct($container);

        $this->wizard = $wizard;
    }

    public function getDefaultDriver(): string
    {
        return $this->config['wizard.driver'];
    }

    protected function createSessionDriver(): Cache
    {
        return new SessionStore(
            $this->wizard,
            $this->container['session.store']
        );
    }

    protected function createDatabaseDriver(): Cache
    {
        return new DatabaseStore(
            $this->wizard,
            $this->getDatabaseConnection(),
            $this->config['wizard.table'],
            $this->container
        );
    }

    protected function getDatabaseConnection(): Connection
    {
        return $this->container['db']->connection(
            $this->config['wizard.connection']
        );
    }

    public function setWizard(Wizard $wizard)
    {
        $this->wizard = $wizard;

        return $this;
    }
}
