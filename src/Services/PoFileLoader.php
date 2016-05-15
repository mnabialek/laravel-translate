<?php

namespace Mnabialek\LaravelTranslate\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\LoaderInterface;
use Mnabialek\LaravelTranslate\Services\Contracts\FileReader;

class PoFileLoader extends FileLoader implements LoaderInterface
{
    /**
     * @var FileReader
     */
    protected $reader;

    /**
     * PoFileLoader constructor.
     *
     * @param Filesystem $files
     * @param string $path
     * @param FileReader $reader
     */
    public function __construct(Filesystem $files, $path, FileReader $reader)
    {
        parent::__construct($files, $path);
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadNamespaceOverrides(
        array $lines,
        $locale,
        $group,
        $namespace
    ) {
        $file = "{$this->path}/vendor/{$namespace}/{$locale}/{$group}.po";

        if ($this->files->exists($file)) {
            return array_replace_recursive($lines,
                $this->reader->get($file));
        }

        return $lines;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadPath($path, $locale, $group)
    {
        if ($this->files->exists($full = "{$path}/{$locale}/{$group}.po")) {
            return $this->reader->get($full);
        }

        return [];
    }
}
