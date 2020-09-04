<?php

namespace Ycs77\LaravelWizard\Contracts;

interface Cache
{
    public function get(string $key = '');

    public function set(array $data, int $lastIndex = null): self;

    public function getLastProcessedIndex(): ?int;

    public function setLastProcessedIndex(int $index): self;

    public function put(string $key, $value, int $lastIndex = null): self;

    public function has(string $key): bool;

    public function clear(): self;
}
