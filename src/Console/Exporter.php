<?php

namespace Mnabialek\LaravelTranslate\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Mnabialek\LaravelTranslate\Services\PotFileWriter;
use Symfony\Component\Finder\Finder;

abstract class Exporter extends Command
{
    /**
     * @var Config
     */
    protected $config;

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
     * Class constructor.
     *
     * @param Container $app
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
     * Verify if config is published and has correct keys
     *
     * @return bool
     */
    protected function hasCorrectConfig()
    {
        foreach ($this->getRequiredConfigKeys() as $key) {
            if (!$this->config->has('translator.' . $key)) {
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
            'extract_directory',
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
     * Save translations into PO/POT files
     *
     * @param array $groupTranslations
     * @param string $outputDir
     * @param string $format
     * @param string|null $lang
     */
    protected function saveTranslations(
        array $groupTranslations,
        $outputDir,
        $format = 'pot',
        $lang = null
    ) {
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        foreach ($groupTranslations as $group => $translations) {
            if ($translations) {
                $this->potWriter->save($this->files,
                    $outputDir . DIRECTORY_SEPARATOR . $group . '.' . $format,
                    $translations, $lang);
                $this->info("File {$group}.{$format} saved");
            } else {
                $this->comment("File {$group}.{$format} with NO translations was not saved");
            }
        }
    }

    /**
     * Get group name for single file mode
     *
     * @return string
     */
    protected function getSingleGroupName()
    {
        return $this->config->get('translator.single_file_name');
    }
}
