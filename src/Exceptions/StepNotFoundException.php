<?php

namespace Ycs77\LaravelWizard\Exceptions;

use Exception;

class StepNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct('Step not found', 404);
    }
}
