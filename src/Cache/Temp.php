<?php

namespace Ycs77\LaravelWizard\Cache;

class Temp
{
    /** @var string */
    public $path;

    /** @var string */
    public $fullPath;

    public function __construct(string $path, string $fullPath)
    {
        $this->path = $path;
        $this->fullPath = $fullPath;
    }
}
