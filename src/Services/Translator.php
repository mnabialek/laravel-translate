<?php

namespace Mnabialek\LaravelTranslate\Services;

class Translator extends \Illuminate\Translation\Translator
{
    /**
     * {@inheritdoc}
     */
    public function parseKey($key)
    {
        $segments = parent::parseKey($key);

        // if key does not contain any . we use default group
        if (!str_contains($key, '.')) {
            $segments[2] = $segments[1];
            $segments[1] = $this->getDefaultGroupName();
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
        return 'messages';
    }
}
