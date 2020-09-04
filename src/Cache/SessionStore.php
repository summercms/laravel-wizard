<?php

namespace Ycs77\LaravelWizard\Cache;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Arr;
use Ycs77\LaravelWizard\Contracts\Cache as CacheContract;
use Ycs77\LaravelWizard\Wizard;

class SessionStore implements CacheContract
{
    use Concerns\HasLastProcessedIndex;

    /** @var Wizard */
    protected $wizard;

    /** @var Session */
    protected $session;

    public function __construct(Wizard $wizard, Session $session)
    {
        $this->wizard = $wizard;
        $this->session = $session;
    }

    public function get(string $key = '')
    {
        $data = $this->session->get($this->key(), []);

        return $key ? Arr::get($data, $key) : $data;
    }

    public function set(array $data, int $lastIndex = null): self
    {
        if (isset($lastIndex)) {
            $data['_last_index'] = (int) $lastIndex;
        }

        $this->session->put($this->key(), $data);

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
        $data = $this->session->get($this->key(), []);

        return Arr::has($data, $key);
    }

    public function clear(): self
    {
        $this->session->forget($this->key());

        return $this;
    }

    protected function key()
    {
        return 'wizard:'.$this->wizard->name();
    }
}
