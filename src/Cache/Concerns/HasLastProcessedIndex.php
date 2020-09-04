<?php

namespace Ycs77\LaravelWizard\Cache\Concerns;

trait HasLastProcessedIndex
{
    public function getLastProcessedIndex(): ?int
    {
        return $this->get('_last_index');
    }

    public function setLastProcessedIndex(int $index): self
    {
        $this->set($this->get(), $index);

        return $this;
    }
}

