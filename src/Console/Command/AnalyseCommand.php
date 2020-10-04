<?php declare(strict_types=1);

namespace Phrozer\Console\Command;

use Ahc\Cli\Input\Command;
use Phrozer\Runner\TaskExecutor;
use Phrozer\Console\CliInput;
use RuntimeException;

/**
 * @property-read string $dir
 * @property-read string[] $dirs
 * @property-read string|null $exclude
 * @property-read string|null $ext
 */
final class AnalyseCommand extends Command
{

    /** @var TaskExecutor */
    private $exec;

    public function __construct(TaskExecutor $exec)
    {
        parent::__construct('analyse', 'Analyse PHP source code');

        $this->arguments('<dir> [dirs...]');
        $this->option('-e --exclude', 'List of excluded directories, separated by a comma (,)');
        $this->option('--ext', 'List of parsed file extensions, separated by a comma (,)');


        $usage[] = '<bold>  analyse</end> <comment>.</end> ## example 1<eol/>';
        $usage[] = '<bold>  analyse</end> <comment>src tests --exclude=tests/data</end> ## example 2<eol/>';
        $usage[] = '<bold>  analyse</end> <comment>. -e tests/data,tests/**/example</end> ## example 3<eol/>';
        $this->usage(implode('', $usage));

        $this->exec = $exec;
    }

    /** @return void */
    public function execute()
    {
        $dirs = array_merge([$this->dir], $this->dirs);
        $cliData = new CliInput($dirs, $this->exclude, $this->ext);
        $this->exec->process($cliData);
    }

    protected function validate()
    {
        try {
            parent::validate();
        } catch (RuntimeException $exc) {
            $this->io()->eol();
            $this->io()->bgRed($exc->getMessage(), true)->eol(2);
            $this->showHelp();
        }
    }
}
