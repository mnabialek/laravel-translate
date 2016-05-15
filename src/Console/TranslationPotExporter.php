<?php

namespace Mnabialek\LaravelTranslate\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Mnabialek\LaravelTranslate\Services\PotFileWriter;
use Symfony\Component\Finder\Finder;
use Illuminate\Contracts\Config\Repository as Config;

class TranslationPotExporter extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'translator:export';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Export all used translations into POT format';

    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var PotFileWriter
     */
    protected $potWriter;

    /**
     * @var Config
     */
    protected $config;

    /**
     * TranslationPotExporter constructor.
     *
     * @param Finder $finder
     * @param Filesystem $files
     * @param PotFileWriter $potWriter
     * @param Config $config
     */
    public function __construct(
        Finder $finder,
        Filesystem $files,
        PotFileWriter $potWriter,
        Config $config
    ) {
        parent::__construct();
        $this->finder = $finder;
        $this->files = $files;
        $this->potWriter = $potWriter;
        $this->config = $config;
    }

    /**
     * Handle command
     */
    public function handle()
    {
        // initialize finder to find correct files
        $this->initializeFinder();

        // find translations in files
        $translations = $this->findTranslations();

        // save translations into POT file
        $this->saveTranslations($translations);
    }

    /**
     * Initialize finder
     */
    protected function initializeFinder()
    {
        $this->finder->in($this->config->get('translator.pot.paths'))
            ->exclude($this->config->get('translator.pot.excluded_paths'));

        foreach ($this->config->get('translator.pot.files') as $file) {
            $this->finder->name($file);
        }

        $this->finder->files();
    }

    /**
     * Find translations
     *
     * @return array
     */
    protected function findTranslations()
    {
        $translations = [];

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($this->finder as $file) {
            if (in_array($file->getRealPath(),
                $this->config->get('translator.pot.ignored_files'))) {
                continue;
            }

            if (preg_match_all('/' . $this->getPattern() . '/siU',
                $file->getContents(), $matches)) {
                foreach ($matches[4] as $ind => $match) {
                    if (!$this->singleModeOn()) {
                        if ($match == '') {
                            // there's no domain set, so we us default one
                            $match = $matches[3][$ind];
                            $group = 'messages';
                        } else {
                            // we have domain set so we use it
                            $group = $matches[3][$ind];
                        }

                        $translations[$group][] = $match;
                    } else {
                        $translations[] = (($match == '') ? $matches[3][$ind]
                            : $matches[3][$ind] . '.' . $match);
                    }
                }
            }
        }

        return $translations;
    }

    /**
     * Save translations into POT files
     *
     * @param array $groupTranslations
     */
    protected function saveTranslations(array $groupTranslations)
    {
        $outputDir = $this->config->get('translator.pot.output_directory');

        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        if (!$this->singleModeOn()) {
            foreach ($groupTranslations as $group => $translations) {
                $translations = array_unique($translations);

                if ($translations) {
                    $this->potWriter->save($this->files,
                        $outputDir . DIRECTORY_SEPARATOR . $group . '.pot',
                        array_fill_keys($translations, ''));
                    $this->info("File {$group}.pot saved");
                }
            }
        } else {
            $fileName = $this->config->get('translator.single_file_name');
            $translations = array_unique($groupTranslations);
            $this->potWriter->save($this->files,
                $outputDir . DIRECTORY_SEPARATOR . $fileName. '.pot',
                array_fill_keys($translations, ''));
            $this->info("File {$fileName}.pot saved");
        }
    }

    /**
     * Get pattern to find trans strings in files
     *
     * @return string
     */
    protected function getPattern()
    {
        return
            '[^\w]' . // not preceded by word
            '(' . implode('|', $this->getFunctions()) . ')' . // trans functions
            '\(' . // function ( character
            '\s*' . // white space characters
            '[\'"]' . // single or double quote
            '(' . // starting capturing group
            '([\w\s-]+?)' . // word, white character or _ or -
            '[.]?' . // optional .
            '([\w\s-]+)?' . // optional word, white character or _ or -
            ')' . // end capturing group 
            '[\'"]' . // closing single or double quote
            '\s*[),]'; // optional whitespaces followed by ) or , 
    }

    /**
     * Get trans functions that will be used to find translations string in
     * files
     *
     * @return array
     */
    protected function getFunctions()
    {
        return [
            'trans',
            'trans_choice',
            'Lang::get',
            'Lang::trans',
            'Lang::choice',
            'Lang::transChoice',
        ];
    }

    /**
     * Whether single POT file mode is enabled
     *
     * @return bool
     */
    protected function singleModeOn()
    {
        return (bool)$this->config->get('translator.single_file');
    }
}
