<?php

namespace Mnabialek\LaravelTranslate\Models;

class Translation
{
    /**
     * Translation key
     *
     * @var string
     */
    protected $key;

    /**
     * Translation value
     *
     * @var string
     */
    protected $value = '';

    /**
     * Whether pluralization should be used
     *
     * @var bool
     */
    protected $plural = false;

    /**
     * Available placeholders for translation
     *
     * @var array
     */
    protected $placeholders = [];

    /**
     * Occurrences where this translation is used
     *
     * @var array
     */
    protected $files = [];

    /**
     * Set key
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Set plural
     *
     * @param bool $plural
     */
    public function setPlural($plural)
    {
        $this->plural = (bool)$plural;
    }

    /**
     * Set translation placeholders
     *
     * @param array $placeholders
     */
    public function setPlaceholders(array $placeholders)
    {
        $this->placeholders = $placeholders;
    }

    /**
     * Add files where this translation is used
     *
     * @param array $files
     */
    public function addFiles(array $files)
    {
        $this->files = array_merge($this->files, $files);
    }

    /**
     * Get translation key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get used placeholders
     *
     * @return array
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * Get information whether pluralization should be use
     *
     * @return bool
     */
    public function getPlural()
    {
        return $this->plural;
    }

    /**
     * Set value
     *
     * @param $value
     *
     * @return string
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get files in which this translation is used
     *
     * @return array
     */
    public function getFiles()
    {
        return array_unique($this->files);
    }
}
