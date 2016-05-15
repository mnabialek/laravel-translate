<?php

namespace Mnabialek\LaravelTranslate\Services;

use Illuminate\Filesystem\Filesystem;

/**
 * Class PotFileWriter
 *
 * @package Mnabialek\LaravelTranslate\Services
 *
 * This class code is based on below PoFileDumper file
 *
 * @see \Symfony\Component\Translation\Dumper\PoFileDumper
 */
class PotFileWriter
{

    /**
     * Save given translations into given file
     *
     * @param Filesystem $files
     * @param string $file
     * @param array $keys
     */
    public function save(Filesystem $files, $file, $keys)
    {
        $output = 'msgid ""' . "\n";
        $output .= 'msgstr ""' . "\n";
        $output .= '"Content-Type: text/plain; charset=UTF-8\n"' . "\n";
        $output .= '"Content-Transfer-Encoding: 8bit\n"' . "\n";
        $output .= "\n";

        $newLine = false;
        foreach ($keys as $key => $value) {
            if ($newLine) {
                $output .= "\n";
            } else {
                $newLine = true;
            }
            $output .= sprintf('msgid "%s"' . "\n", $this->escape($key));
            $output .= sprintf('msgstr "%s"', $this->escape($value));
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
