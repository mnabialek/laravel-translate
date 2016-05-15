<?php

namespace Mnabialek\LaravelTranslate\Services\Contracts;

interface FileReader
{
    /**
     * Get all available translations from PO file
     *
     * @param string $file
     *
     * @return array
     */
    public function get($file);
}
