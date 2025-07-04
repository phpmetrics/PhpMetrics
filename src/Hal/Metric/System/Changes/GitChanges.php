<?php

namespace Hal\Metric\System\Changes;

use Hal\Application\Config\Config;
use Hal\Application\Config\ConfigException;
use Hal\Metric\FileMetric;
use Hal\Metric\Metrics;
use Hal\Metric\ProjectMetric;

class GitChanges
{
    /**
     * @var array
     */
    private $files = [];

    /**
     * @var Config
     */
    private $config;

    /**
     * @param array $files
     */
    public function __construct(Config $config, array $files)
    {
        $this->files = $files;
        $this->config = $config;
    }

    /**
     * @param Metrics $metrics
     * @throws ConfigException
     */
    public function calculate(Metrics $metrics)
    {
        if (!$this->config->has('git')) {
            return;
        }

        $bin = $this->config->get('git');
        if (is_bool($bin)) {
            $bin = 'git';
        }

        if (count($this->files) == 0) {
            return;
        }

        $r = shell_exec(sprintf('%s --version', $bin));
        if (!preg_match('!git version!', $r)) {
            throw new ConfigException(sprintf('Git binary (%s) incorrect', $bin));
        }

        // get all history (for only on directory for the moment
        // @todo: git history for multiple repositories
        // 500 last commits max
        $file = current($this->files);
        $command = sprintf(
            "cd %s && %s log --format='* %%at\t%%cn' --numstat -n 500",
            escapeshellarg(realpath(dirname($file))),
            escapeshellarg($bin)
        );
        $r = shell_exec($command);
        $r = array_filter(explode(PHP_EOL, $r));

        // build a range of commits info, stepped by week number
        $history = [];
        $dateFormat = 'Y-W';

        // calculate statistics
        $firstCommitDate = null;
        $commitsByFile = [];
        $localFiles = [];
        $localFiles['additions'] = 0;
        $localFiles['removes'] = 0;
        $localFiles['nbFiles'] = 0;
        $authors = [];

        foreach ($r as $line) {
            if (preg_match('!^\* (\d+)\s+(.*)!', $line, $matches)) {
                // head line

                if (isset($date)) {
                    // new head line ($author is set). Consolidate now for last commit
                    if (!isset($history[$date])) {
                        $history[$date] = ['nbFiles' => 0, 'additions' => 0, 'removes' => 0];
                    }
                    $history[$date]['nbFiles'] += $localFiles['nbFiles'];
                    $history[$date]['additions'] += $localFiles['additions'];
                    $history[$date]['removes'] += $localFiles['removes'];

                    // reset
                    $localFiles['additions'] = 0;
                    $localFiles['removes'] = 0;
                    $localFiles['nbFiles'] = 0;
                }

                // new infos
                list(, $timestamp, $author) = $matches;
                $date = (new \DateTime())->setTimestamp($timestamp)->format($dateFormat);

                if ($firstCommitDate === null) {
                    $firstCommitDate = $timestamp;
                }

                // author
                if (!isset($authors[$author])) {
                    $authors[$author] = ['nbFiles' => 0, 'commits' => 0, 'additions' => 0, 'removes' => 0];
                }
                $authors[$author]['commits']++;
            } else {
                if (preg_match('!(\d+)\s+(\d+)\s+(.*)!', $line, $matches)) {
                    // additions and changes for each file
                    list(, $additions, $removes, $filename) = $matches;

                    if (!$this->doesThisFileShouldBeCounted($filename)) {
                        // we don't care about all files
                        continue;
                    }

                    // global history
                    $localFiles['additions'] += $additions;
                    $localFiles['removes'] += $removes;
                    $localFiles['nbFiles']++;

                    // commits by file
                    if (!isset($commitsByFile[$filename])) {
                        $commitsByFile[$filename] = 0;
                    }
                    $commitsByFile[$filename]++;

                    // author
                    if (isset($author)) {
                        $authors[$author]['nbFiles']++;
                        $authors[$author]['additions'] += $additions;
                        $authors[$author]['removes'] += $removes;
                    }
                }
            }
        }

        // build a range of dates since first commit
        // (pad weeks without any commit)
        $current = $firstCommitDate;
        $last = time();
        while ($current <= $last) {
            $key = date($dateFormat, $current);
            if (!isset($history[$key])) {
                $history[$key] = [
                    'nbFiles' => 0,
                    'additions' => 0,
                    'removes' => 0,
                ];
            }
            $current = strtotime('+7 day', $current);
        }

        // store results
        $result = new ProjectMetric('git');
        $result->set('history', $history);
        $result->set('authors', $authors);
        $metrics->attach($result);

        foreach ($commitsByFile as $filename => $nbCommits) {
            $info = new FileMetric($filename);
            $info->set('gitChanges', $nbCommits);
            $metrics->attach($info);
        }
    }

    /**
     * @param $file
     * @return int
     */
    private function doesThisFileShouldBeCounted($file)
    {
        return preg_match('!\.(php|inc)$!i', $file);
    }
}
