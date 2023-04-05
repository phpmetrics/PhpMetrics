<?php
declare(strict_types=1);

namespace Hal\Application\Config;

use Hal\Application\Config\File\ConfigFileReaderFactoryInterface;
use function array_pop;
use function array_shift;
use function explode;
use function in_array;
use function preg_match;
use function str_ends_with;
use function str_starts_with;
use function trim;

/**
 * Configuration parser.
 */
final class Parser implements ParserInterface
{
    public function __construct(private readonly ConfigFileReaderFactoryInterface $configFileReaderFactory)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function parse(array $argv): Config
    {
        $config = new Config();

        if ([] === $argv) {
            return $config;
        }

        if (
            str_ends_with($argv[0], '.php') ||
            str_ends_with($argv[0], 'phpmetrics') ||
            str_ends_with($argv[0], 'phpmetrics.phar')
        ) {
            array_shift($argv);
        }

        // Checking for a configuration file option key and importing options
        foreach ($argv as $k => $arg) {
            if (1 === preg_match('!--config=(.*)!', $arg, $matches)) {
                [, $filename] = $matches;
                $this->configFileReaderFactory->createFromFileName($filename)->read($config);
                unset($argv[$k]);
            }
        }

        // arguments with options
        foreach ($argv as $k => $arg) {
            if (1 === preg_match('!--([\w\-]+)=(.*)!', $arg, $matches)) {
                [, $parameter, $value] = $matches;
                $config->set($parameter, trim($value, ' "\''));
                unset($argv[$k]);
            }
        }

        // arguments without options
        foreach ($argv as $k => $arg) {
            if (1 === preg_match('!--([\w\-]+)$!', $arg, $matches)) {
                [, $parameter] = $matches;
                $config->set($parameter, true);
                unset($argv[$k]);
            }
        }

        // last argument
        $files = array_pop($argv);
        if (!in_array($files, [null, ''], true) && !str_starts_with($files, '--')) {
            $config->set('files', explode(',', $files));
        }

        return $config;
    }
}
