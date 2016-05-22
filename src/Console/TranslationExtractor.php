<?php

namespace Mnabialek\LaravelTranslate\Console;

use Mnabialek\LaravelTranslate\Models\Translation;

class TranslationExtractor extends Exporter
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'translator:extract';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Extract all existing translations keys so they could be added into POT file';

    /**
     * Handle command
     */
    public function handle()
    {
        // verify config file
        if (!$this->hasCorrectConfig()) {
            $this->error('Config is invalid or not published. You should export it or modify. See readme.md for details');

            return;
        }

        // initialize finder to find correct files
        $this->initializeFinder();

        // find translations in files
        list($translations, $additional) = $this->getTranslations();

        $outputDir = $this->config->get('translator.extract_directory');

        // save translations into PO file
        foreach ($translations as $lang => $trans) {
            $this->comment("Saving for '" . $lang . "' directory...");
            $this->saveTranslations($trans,
                $outputDir . DIRECTORY_SEPARATOR . $lang, 'po', $lang);
        }

        // save translations that should be added manually into POT file
        $this->comment('Saving file with additional translations');
        $this->saveAdditionalKeys($additional, $outputDir, 'additional.txt');

        $this->comment('You should use those files into your translation');
    }

    /**
     * Save additional translation keys
     *
     * @param array $additional
     * @param string $outputDir
     * @param string $fileName
     */
    protected function saveAdditionalKeys(
        array $additional,
        $outputDir,
        $fileName
    ) {
        $this->files->put($outputDir . DIRECTORY_SEPARATOR . $fileName,
            implode("\n", array_map(function ($val) {
                return "'{$val}',";
            }, $additional)));

        $this->info("File $fileName with extra translations was saved");
    }

    /**
     * Initialize finder
     */
    protected function initializeFinder()
    {
        $this->finder->in($this->getLangDirectory())->name('*.php')
            ->files();
    }

    /**
     * Get language directory
     *
     * @return string
     */
    protected function getLangDirectory()
    {
        return $this->getLaravel()['path.lang'];
    }

    /**
     * Get translations from resource language files
     *
     * @return array
     */
    protected function getTranslations()
    {
        $translations = [];
        $additionalKeys = [];

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($this->finder as $file) {

            // set filename and lang
            $fileContent = require($file->getRealPath());
            $fileName = $file->getBasename('.php');
            $filePath = $file->getPath();
            $lang = mb_substr($filePath,
                mb_strlen($this->getLangDirectory()) + 1);

            // additional translation keys
            $additionalKeys += array_keys(array_dot($fileContent,
                $fileName . '.'));

            $fileTrans = array_dot($fileContent);

            foreach ($fileTrans as $key => $singleTranslation) {
                $t = new Translation();
                $t->setValue($singleTranslation);

                $t->setPlaceholders(collect(explode(' ',
                    $singleTranslation))->filter(function ($val) {
                    return preg_match('/:(\w+).*?/is', $val);
                })->map(function ($val) {
                    preg_match('/:(\w+).*?/is', $val, $matches);

                    return $matches[1];
                })->all());

                if ($this->singleModeOn()) {
                    $key = $fileName . $key;
                    $group = $this->getSingleGroupName();
                } else {
                    $group = $fileName;
                }
                $t->setKey($key);
                $translations[$lang][$group][] = $t;
            }
        }

        return [$translations, $additionalKeys];
    }
}
