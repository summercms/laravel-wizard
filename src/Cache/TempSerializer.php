<?php

namespace Ycs77\LaravelWizard\Cache;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Filesystem\Factory as Filesystem;
use Illuminate\Http\UploadedFile;
use Ycs77\LaravelWizard\Contracts\TempSerializer as TempSerializerContract;

class TempSerializer implements TempSerializerContract
{
    /** @var Config */
    protected $config;

    /** @var Filesystem */
    protected $filesystem;

    public function __construct(Config $config, Filesystem $filesystem)
    {
        $this->config = $config;
        $this->filesystem = $filesystem;
    }

    public function serialize(UploadedFile $file): Temp
    {
        $path = $file->store($this->folder(), $this->driver());
        $fullPath = $this->filesystem->disk($this->driver())->path($path);

        return new Temp($path, $fullPath);
    }

    public function unserialize(Temp $temp): UploadedFile
    {
        return new UploadedFile($temp->fullPath, base_path($temp->path));
    }

    protected function driver(): string
    {
        return $this->config->get('wizard.temporary_driver');
    }

    protected function folder(): string
    {
        return $this->config->get('wizard.temporary_folder');
    }
}
