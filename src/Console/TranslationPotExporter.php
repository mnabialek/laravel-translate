<?php

namespace Mnabialek\LaravelTranslate\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Mnabialek\LaravelTranslate\Models\Translation;
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
     * @param Config $config
     */
    public function __construct(
        Container $app,
        Finder $finder,
        Filesystem $files,
        Config $config
    ) {
        parent::__construct();
        $this->finder = $finder;
        $this->files = $files;
        $this->config = $config;
        $this->setLaravel($app);
        $this->potWriter = $this->getLaravel()->make(PotFileWriter::class,
            ['config' => $config->get('translator.pot')]);
    }

    /**
     * Handle command
     */
    public function handle()
    {
        // verify config file
        if ($this->hasCorrectConfig()) {
            $this->error('Config is invalid or not exported. You should export it or modify. See readme.md for details');

            return;
        }

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
        $usedTranslations = [];

        // first we will find translations and add them
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($this->finder as $file) {
            if (in_array($file->getRealPath(),
                $this->config->get('translator.pot.ignored_files'))) {
                continue;
            }

            $content = $file->getContents();

            $lines = explode("\n", $content);

            if (preg_match_all('/' . $this->getPattern() . '/siU',
                $content, $matches)) {
                foreach ($matches[4] as $ind => $match) {
                    $t = new Translation();

                    // get this translation locations in current file
                    $locations = [];
                    $location = mb_substr($file->getRealPath(),
                        mb_strlen(base_path()) + 1);
                    foreach ($lines as $nr => $line) {
                        if (str_contains($line, $matches[0][$ind])) {
                            $locations[] = $location . ':' . ($nr + 1);
                        }
                    }

                    // set as plural if plural function was used
                    if (in_array($matches[1][$ind],
                        $this->getPluralFunctions())) {
                        $t->setPlural(true);
                    }

                    // find any placeholders and fill them
                    if (isset($matches[5][$ind]) && $matches[5][$ind] != '') {
                        preg_match_all('/\s*[\'"](\w+)[\'"]\s*=\>\s*.*,*/siU',
                            $matches[5][$ind], $placeholders);
                        $t->setPlaceholders($placeholders[1]);
                    }

                    // get translation token and group
                    list($token, $group) =
                        $this->getTokenAndGroup($match, $matches[3][$ind]);

                    // set token
                    $t->setKey($token);
                    // set locations
                    $t->addFiles($locations);

                    $this->addTranslation($t, $group, $locations, $translations,
                        $usedTranslations);
                }
            }
        }

        // now we add additional translations
        $this->addAdditionalTranslations($translations, $usedTranslations);

        return ($this->singleModeOn())
            ? $translations[$this->getSingleDummyGroup()]
            : $translations;
    }

    /**
     * Add single translation
     *
     * @param Translation $trans
     * @param string $group
     * @param array $locations
     * @param array $translations
     * @param array $usedTranslations
     */
    protected function addTranslation(
        Translation $trans,
        $group,
        array $locations,
        array &$translations,
        array &$usedTranslations
    ) {
        // add only if it was not used before
        if (empty($usedTranslations[$group]) ||
            !in_array($trans->getKey(),
                $usedTranslations[$group])
        ) {
            $translations[$group][] = $trans;
            $usedTranslations[$group][] = $trans->getKey();
        } elseif (!empty($translations[$group])) {
            /**@var Translation $v */
            foreach ($translations[$group] as $v) {
                if ($v->getKey() == $trans->getKey()) {
                    // add new occurrences for translation 
                    $v->addFiles($locations);
                }
            }
        }
    }

    /**
     * Add additional translations defined in config file
     *
     * @param array $translations
     * @param array $usedTranslations
     */
    protected function addAdditionalTranslations(
        array &$translations,
        array &$usedTranslations
    ) {
        $additional =
            $this->config->get('translator.pot.additional_translations', []);

        foreach ($additional as $key) {
            if ($this->singleModeOn()) {
                // for single mode we use explicit key
                $group = $this->getSingleDummyGroup();
                $token = $key;
            } else {
                if (str_contains($key, '.')) {
                    // we know group and token
                    list($group, $token) = explode('.', $key, 2);
                } else {
                    // we have no group - we use default one
                    $group =
                        $this->config->get('translator.default_group_name');
                    $token = $key;
                }
            }

            // create translation (without comment etc)
            $trans = new Translation();
            $trans->setKey($token);

            $this->addTranslation($trans, $group, [], $translations,
                $usedTranslations);
        }
    }

    /**
     * Get dummy translation group for single file mode. It helps to simplify
     * code a bit
     *
     * @return string
     */
    protected function getSingleDummyGroup()
    {
        return 'dummy';
    }

    /**
     * Get translation token and group
     *
     * @param string $match
     * @param string $domain
     *
     * @return array
     */
    protected function getTokenAndGroup($match, $domain)
    {
        if (!$this->singleModeOn()) {
            if ($match == '') {
                // there's no domain set, so we us default one
                $token = $domain;
                $group = $this->config->get('translator.default_group_name');
            } else {
                // we have domain set so we use it
                $group = $domain;
                $token = $match;
            }
        } else {
            if ($match == '') {
                $token = $domain;
            } else {
                $token = $domain . '.' . $match;
            }
            $group = $this->getSingleDummyGroup();
        }

        return [$token, $group];
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
                if ($translations) {
                    $this->potWriter->save($this->files,
                        $outputDir . DIRECTORY_SEPARATOR . $group . '.pot',
                        $translations);
                    $this->info("File {$group}.pot saved");
                }
            }
        } else {
            $fileName = $this->config->get('translator.single_file_name');
            $this->potWriter->save($this->files,
                $outputDir . DIRECTORY_SEPARATOR . $fileName . '.pot',
                $groupTranslations);
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
            '[^\w]*' . // not preceded by word
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
            '\s*,*' . // optional whitespaces followed by optional ,
            '\s*' . // white space characters
            '(?:\d*?)' . // optional number
            ',*\s*' . // optional , and white space characters
            '(?:\[(.*)\])*' .// optional extra parameters as array
            '\)'; // function ) character
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
     * Get trans choice functions
     *
     * @return array
     */
    protected function getPluralFunctions()
    {
        return [
            'trans_choice',
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

    /**
     * Verify if config is published and has correct keys
     *
     * @return bool
     */
    protected function hasCorrectConfig()
    {
        foreach ($this->getRequiredConfigKeys() as $key) {
            if ($this->config->has('translator.' . $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get required config keys
     *
     * @return array
     */
    protected function getRequiredConfigKeys()
    {
        return [
            'single_file',
            'single_file_name',
            'default_group_name',
            'pot.paths',
            'pot.excluded_paths',
            'pot.files',
            'pot.ignored_files',
            'pot.output_directory',
            'pot.base_path',
            'pot.comments.add',
            'pot.comments.plural_text',
            'pot.comments.placeholders_text',
            'pot.additional_translations',
        ];
    }
}
