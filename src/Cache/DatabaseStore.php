<?php

namespace Ycs77\LaravelWizard\Cache;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Ycs77\LaravelWizard\Contracts\Cache as CacheContract;
use Ycs77\LaravelWizard\Wizard;

class DatabaseStore implements CacheContract
{
    use Concerns\HasLastProcessedIndex;

    /** @var Wizard */
    protected $wizard;

    /** @var ConnectionInterface */
    protected $connection;

    /** @var string */
    protected $table;

    /** @var Container */
    protected $container;

    public function __construct(Wizard $wizard,
        ConnectionInterface $connection,
        $table,
        Container $container)
    {
        $this->wizard = $wizard;
        $this->connection = $connection;
        $this->table = $table;
        $this->container = $container;
    }

    public function get(string $key = '')
    {
        if (! $data = (array) $this->getSelectedQuery()->first()) {
            return;
        }

        $data = json_decode($data['payload'], true);

        return $key ? Arr::get($data, $key) : $data;
    }

    public function set(array $data, int $lastIndex = null): self
    {
        if (isset($lastIndex)) {
            $data['_last_index'] = $lastIndex;
        }

        $attributes = $this->userId()
            ? ['user_id' => $this->userId()]
            : ['ip_address' => $this->ipAddress()];

        $this->getQuery()->updateOrInsert($attributes, [
            'wizard' => $this->wizard->name(),
            'payload' => json_encode($data),
        ]);

        return $this;
    }

    public function put(string $key, $value, int $lastIndex = null): self
    {
        $data = $this->get();
        Arr::set($data, $key, $value);
        $this->set($data, $lastIndex);

        return $this;
    }

    public function has(string $key): bool
    {
        $data = $this->get($key);

        return isset($data);
    }

    public function clear(): self
    {
        $this->getSelectedQuery()->delete();

        return $this;
    }

    /**
     * @return int|string|null
     */
    protected function userId()
    {
        return $this->container->make(Guard::class)->id();
    }

    protected function ipAddress(): string
    {
        return $this->container->make('request')->ip();
    }

    protected function getQuery(): Builder
    {
        return $this->connection->table($this->table);
    }

    protected function getSelectedQuery(): Builder
    {
        $query = $this->getQuery()->where('wizard', $this->wizard->name());

        return $this->userId()
            ? $query->where('user_id', $this->userId())
            : $query->where('ip_address', $this->ipAddress());
    }
}
