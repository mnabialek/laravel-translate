<?php

namespace Mnabialek\LaravelTranslate\Services;

use Illuminate\Translation\LoaderInterface;

class Translator extends \Illuminate\Translation\Translator
{
    /**
     * @var bool
     */
    protected $useSingleFile;

    /**
     * @var string
     */
    protected $singleFileName;

    /**
     * @var string
     */
    protected $defaultGroupName;

    /**
     * Create a new translator instance.
     *
     * @param  \Illuminate\Translation\LoaderInterface $loader
     * @param  string $locale
     * @param bool $useSingleFile
     * @param string $singleFileName
     * @param string $defaultGroupName
     */
    public function __construct(
        LoaderInterface $loader,
        $locale,
        $useSingleFile = false,
        $singleFileName = 'messages',
        $defaultGroupName = 'messages'
    ) {
        parent::__construct($loader, $locale);
        $this->useSingleFile = $useSingleFile;
        $this->singleFileName = $singleFileName;
        $this->defaultGroupName = $defaultGroupName;
    }

    /**
     * {@inheritdoc}
     */
    public function parseKey($key)
    {
        $segments = parent::parseKey($key);

        if (!$this->useSingleFile) {
            // if key does not contain any . we use default group
            if (!str_contains($key, '.')) {
                $segments[2] = $segments[1];
                $segments[1] = $this->getDefaultGroupName();
            }
        } else {
            // if single file, we use the whole group and item as item
            // and for group we use single file name
            $segments[2] = $segments[2] ? $segments[1] . '.' . $segments[2]
                : $segments[1];
            $segments[1] = $this->getSingleFileName();
        }

        return $segments;
    }

    /**
     * Get default group name
     *
     * @return string
     */
    protected function getDefaultGroupName()
    {
        return $this->defaultGroupName;
    }

    /**
     * Get single group name
     *
     * @return string
     */
    protected function getSingleFileName()
    {
        return $this->singleFileName;
    }
}
