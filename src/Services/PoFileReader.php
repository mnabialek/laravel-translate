<?php

namespace Mnabialek\LaravelTranslate\Services;

use Mnabialek\LaravelTranslate\Services\Contracts\FileReader;
use Symfony\Component\Translation\Loader\PoFileLoader;

class PoFileReader extends PoFileLoader implements FileReader
{
    /**
     * Get all available translations from PO file
     *
     * @param string $file
     *
     * @return array
     */
    public function get($file)
    {
        return $this->loadResource($file);
    }
}
