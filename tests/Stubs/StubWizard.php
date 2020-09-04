<?php

namespace Ycs77\LaravelWizard\Test\Stubs;

use Ycs77\LaravelWizard\Wizard;

class StubWizard extends Wizard
{
    /** @var string */
    protected $name = 'ycs77_test';

    /** @var string */
    protected $title = '測試 Wizard';

    protected function steps(): array
    {
        return [];
    }
}
