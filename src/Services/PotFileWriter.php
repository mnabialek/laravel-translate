<?php

namespace Mnabialek\LaravelTranslate\Services;

use Illuminate\Filesystem\Filesystem;
use Mnabialek\LaravelTranslate\Models\Translation;

/**
 * Class PotFileWriter
 *
 * @package Mnabialek\LaravelTranslate\Services
 *
 * This class code is based on below PoFileDumper file
 * @see \Symfony\Component\Translation\Dumper\PoFileDumper
 */
class PotFileWriter
{
    /**
     * @var array
     */
    private $config;

    /**
     * PotFileWriter constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Save given translations into given file
     *
     * @param Filesystem $files
     * @param string $file
     * @param array $matches
     */
    public function save(Filesystem $files, $file, $matches)
    {
        $output = 'msgid ""' . "\n";
        $output .= 'msgstr ""' . "\n";
        $output .= '"Content-Type: text/plain; charset=UTF-8\n"' . "\n";
        $output .= '"Content-Transfer-Encoding: 8bit\n"' . "\n";

        // save Poedit base path if it's enabled in config
        if (($path = $this->config['base_path']) !== null) {
            $output .= '"X-Poedit-Basepath: ' . $path . '"' . "\n";
        }
        $output .= "\n";

        $newLine = false;
        /** @var Translation $match */
        foreach ($matches as $match) {
            if ($newLine) {
                $output .= "\n";
            } else {
                $newLine = true;
            }

            // if there are saved any occurrences of translation write them
            if ($places = $match->getFiles()) {
                $output .= '#: ' . $this->escape(implode(' ', $places)) . "\n";
            }

            // saving comment
            $comment = '';
            if ($this->config['comments']['add']) {
                // adding info about pluralization
                if ($match->getPlural()) {
                    $comment .= $this->config['comments']['plural_text'];
                }

                // adding info about available placeholders
                if ($placeholders = $match->getPlaceholders()) {
                    $comment .= $this->config['comments']['placeholders_text'] .
                        implode(', ', array_map(function ($val) {
                            return ':' . $val;
                        }, $placeholders));
                }
            }
            if ($comment) {
                $output .= '# ' . $this->escape($comment) . "\n";
            }

            // saving translation key and value
            $output .= sprintf('msgid "%s"' . "\n",
                $this->escape($match->getKey()));
            $output .= sprintf('msgstr "%s"' . "\n",
                $this->escape($match->getValue()));
        }

        $files->put($file, $output);
    }

    /**
     * Escape given string
     *
     * @param string $str
     *
     * @return string
     */
    private function escape($str)
    {
        return addcslashes($str, "\0..\37\42\134");
    }
}
