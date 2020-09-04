<?php

namespace Ycs77\LaravelWizard;

use ArrayIterator;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use IteratorAggregate;
use Ycs77\LaravelWizard\Contracts\Cache;
use Ycs77\LaravelWizard\Wizard;

class StepsCollection implements Countable, IteratorAggregate, Arrayable
{
    /** @var Wizard */
    protected $wizard;

    /** @var Cache|null */
    protected $cache;

    /** @var Collection */
    protected $steps;

    /** @var int */
    protected $currentIndex;

    public function __construct(Wizard $wizard)
    {
        $this->wizard = $wizard;
        $this->steps = $this->newCollection();
    }

    public function get(int $key): ?Step
    {
        return $this->steps->get($key);
    }

    public function find(string $slug): ?Step
    {
        return $this->steps
            ->first(function (Step $step) use ($slug) {
                return $step->slug() === $slug;
            });
    }

    public function findKey(string $slug): ?int
    {
        return $this->steps
            ->filter(function (Step $step) use ($slug) {
                return $step->slug() === $slug;
            })
            ->keys()
            ->first(null, 0);
    }

    public function has(int $key): bool
    {
        return $this->steps->has($key);
    }

    public function currentIndex(): ?int
    {
        return optional($this->cache)->getLastProcessedIndex();
    }

    public function current(): ?Step
    {
        return ! is_null($this->currentIndex())
            ? $this->get($this->currentIndex())
            : null;
    }

    public function setCurrent(Step $step): self
    {
        optional($this->cache)->setLastProcessedIndex($step->index());

        return $this;
    }

    public function first(): ?Step
    {
        return $this->steps->first();
    }

    public function last(): ?Step
    {
        return $this->steps->last();
    }

    public function prev(): ?Step
    {
        return ! is_null($this->currentIndex())
            ? $this->get($this->currentIndex() - 1)
            : null;
    }

    public function next(): ?Step
    {
        return ! is_null($this->currentIndex())
            ? $this->get($this->currentIndex() + 1)
            : null;
    }

    public function hasPrev(): bool
    {
        return ! is_null($this->currentIndex())
            ? $this->has($this->currentIndex() - 1)
            : false;
    }

    public function hasNext(): bool
    {
        return ! is_null($this->currentIndex())
            ? $this->has($this->currentIndex() + 1)
            : false;
    }

    public function prevSlug(): ?string
    {
        return $this->hasPrev() ? $this->prev()->slug() : null;
    }

    public function nextSlug(): ?string
    {
        return $this->hasNext() ? $this->next()->slug() : null;
    }

    /** @return array|\Ycs77\LaravelWizard\Step[] */
    public function all(): array
    {
        return $this->steps->all();
    }

    /** @param array|\Ycs77\LaravelWizard\Step[] $steps */
    public function set(array $steps): self
    {
        $this->steps = $this->newCollection($steps);

        return $this;
    }

    /** @param array|\Ycs77\LaravelWizard\Step[] $steps */
    public function newCollection(array $steps = []): Collection
    {
        $steps = Collection::make($steps);
        $isValidSteps = $steps->every(function ($step) {
            return $step instanceof Step;
        });

        if (! $isValidSteps) {
            throw new InvalidArgumentException(sprintf(
                'The $steps values every must instanceof %s.', Step::class
            ));
        }

        return $steps;
    }

    /** @param string|Step $step */
    public function push($step): self
    {
        if (is_string($step)) {
            $step = new $step($this->wizard, $this->steps->keys()->last(null, -1) + 1);
        }

        $this->steps->push($step);

        return $this;
    }

    /** @param array|string[]|Step[] $step */
    public function pushMany(array $steps): self
    {
        /** @var Step $step */
        foreach ($steps as $step) {
            $this->push($step);
        }

        return $this;
    }

    public function forget(int $key): self
    {
        $this->steps->forget((string) $key);

        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->steps->isEmpty();
    }

    public function isNotEmpty(): bool
    {
        return $this->steps->isNotEmpty();
    }

    public function original(): Collection
    {
        return $this->steps;
    }

    public function count(): int
    {
        return $this->steps->count();
    }

    public function toArray(): array
    {
        return $this->all();
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->all());
    }

    public function getCache(): Cache
    {
        return $this->cache;
    }

    public function setCache(Cache $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    public function hasCache(): bool
    {
        return isset($this->cache);
    }

    public function data(string $slug, string $key = null)
    {
        return optional($this->cache)
            ->get($this->getCacheKey($slug, $key));
    }

    public function cacheData(string $slug, string $key = null, array $data, int $lastIndex = null)
    {
        optional($this->cache)
            ->put($this->getCacheKey($slug, $key), $data, $lastIndex);

        return $this;
    }

    public function clearCache()
    {
        optional($this->cache)->clear();

        return $this;
    }

    protected function getCacheKey(string $slug, string $key = null)
    {
        return collect([$slug, $key])->filter()->implode('.');
    }
}
