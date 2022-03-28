<?php
declare(strict_types=1);

namespace Hal\Metric\System\Changes;

use DateTime;
use Hal\Application\Config\ConfigBagInterface;
use Hal\Exception\ConfigException\GitBinaryIsIncorrectException;
use Hal\Metric\CalculableWithFilesInterface;
use Hal\Metric\FileMetric;
use Hal\Metric\Metrics;
use Hal\Metric\ProjectMetric;
use function array_filter;
use function current;
use function date;
use function dirname;
use function escapeshellarg;
use function explode;
use function implode;
use function preg_match;
use function realpath;
use function shell_exec;
use function sprintf;
use function str_contains;
use function strtotime;
use function time;
use const PHP_EOL;

/**
 * This class computes the most changes done via Git on a project.
 */
final class GitChanges implements CalculableWithFilesInterface
{
    /** @var array<int, string> */
    private array $files = [];

    /**
     * @param Metrics $metrics
     * @param ConfigBagInterface $config
     */
    public function __construct(
        private readonly Metrics $metrics,
        private readonly ConfigBagInterface $config
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(): void
    {
        // If configuration does not have the "git" flag enabled or if there is no file to check changes on, there are
        // no calculations to process.
        if ([] === $this->files || !$this->config->has('git')) {
            return;
        }

        $r = shell_exec('git --version');
        if (!str_contains($r, 'git version')) {
            throw GitBinaryIsIncorrectException::invalidCommand();
        }

        // Get all history only for single directory for the moment. TODO: git history for multiple repositories.
        // 500 last commits max.
        // Option -l0 allows unlimited detections of renames overriding the config diff.renameLimit which may vary.
        $file = current($this->files);
        $command = sprintf(
            "cd %s && git log --format='* %%at\t%%cn' --numstat --max-count=500 -l0",
            escapeshellarg(realpath(dirname($file)))
        );
        $r = shell_exec($command);
        $r = array_filter(explode(PHP_EOL, $r));

        // build a range of commits info, stepped by week number
        $history = [];

        // calculate statistics
        $firstCommitDate = null;
        $commitsByFile = [];
        $localFiles = ['additions' => 0, 'removes' => 0, 'nbFiles' => 0];
        $authors = [];

        foreach ($r as $line) {
            if (preg_match('!^\* (\d+)\s+(.*)!', $line, $matches)) {
                // headline

                if (isset($date)) {
                    // new head line ($author is set). Consolidate now for last commit
                    $history += [$date => ['nbFiles' => 0, 'additions' => 0, 'removes' => 0]];
                    $history[$date]['nbFiles'] += $localFiles['nbFiles'];
                    $history[$date]['additions'] += $localFiles['additions'];
                    $history[$date]['removes'] += $localFiles['removes'];

                    // reset
                    $localFiles['additions'] = 0;
                    $localFiles['removes'] = 0;
                    $localFiles['nbFiles'] = 0;
                }

                // new infos, by week.
                [, $timestamp, $author] = $matches;
                $timestamp = (int)$timestamp;
                $date = (new DateTime())->setTimestamp($timestamp)->format('Y-W');

                if (null === $firstCommitDate) {
                    $firstCommitDate = $timestamp;
                }

                // author
                if (!isset($authors[$author])) {
                    $authors[$author] = ['nbFiles' => 0, 'commits' => 0, 'additions' => 0, 'removes' => 0];
                }
                $authors[$author]['commits']++;
            } elseif (preg_match('!(\d+)\s+(\d+)\s+(.*)!', $line, $matches)) {
                // additions and changes for each file
                [, $additions, $removes, $filename] = $matches;
                if (!$this->doesThisFileShouldBeCounted($filename)) {
                    // we don't care about all files
                    continue;
                }

                // global history
                $localFiles['additions'] += $additions;
                $localFiles['removes'] += $removes;
                ++$localFiles['nbFiles'];

                // commits by file
                $commitsByFile += [$filename => 0];
                ++$commitsByFile[$filename];

                // author
                if (isset($author)) {
                    ++$authors[$author]['nbFiles'];
                    $authors[$author]['additions'] += $additions;
                    $authors[$author]['removes'] += $removes;
                }
            }
        }

        // build a range of dates since first commit
        // (pad weeks without any commit)
        $current = $firstCommitDate;
        $last = time();
        while ($current <= $last) {
            $history += [date('Y-W', $current) => ['nbFiles' => 0, 'additions' => 0, 'removes' => 0]];
            $current = strtotime('+7 day', $current);
        }

        // store results
        $result = new ProjectMetric('git');
        $result->set('history', $history);
        $result->set('authors', $authors);
        $this->metrics->attach($result);

        foreach ($commitsByFile as $filename => $nbCommits) {
            $info = new FileMetric($filename);
            $info->set('gitChanges', $nbCommits);
            $this->metrics->attach($info);
        }
    }

    /**
     * @param string $file
     * @return bool
     */
    private function doesThisFileShouldBeCounted(string $file): bool
    {
        return (bool)preg_match('!\.(' . implode('|', $this->config->get('extensions')) . ')$!i', $file);
    }
}
