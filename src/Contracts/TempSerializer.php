<?php

namespace Ycs77\LaravelWizard\Contracts;

use Illuminate\Http\UploadedFile;
use Ycs77\LaravelWizard\Cache\Temp;

interface TempSerializer
{
    public function serialize(UploadedFile $file): Temp;

    public function unserialize(Temp $temp): UploadedFile;
}
