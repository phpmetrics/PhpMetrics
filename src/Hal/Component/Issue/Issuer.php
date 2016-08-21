<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Issue;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Issuer
 * @package Hal\Component\Issue
 */
class Issuer
{

    /**
     * @var array
     */
    private $debug = [];

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Issuer constructor.
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @throws \ErrorException
     */
    public function onError($errno, $errstr, $errfile, $errline)
    {
        $php = PHP_VERSION;
        $os = php_uname();
        $phpmetrics = getVersion();
        $traces = debug_backtrace(0, 10);
        $trace = '';
        foreach ($traces as $c) {
            if (isset($c['file'])) {
                $trace .= sprintf("+ %s (line %d)\n", $c['file'], $c['line']);
            }
        }

        $debug = '';
        foreach ($this->debug as $key => $value) {
            if ($value instanceof Node || is_array($value)) {
                $value = (new Standard())->prettyPrint($value);
            }

            $debug .= sprintf("%s: %s\n", $key, $value);
        }

        $message = <<<EOT

<error>An unexpected error occured. Can you open a new issue following this link please ?</error>

## Link: https://github.com/phpmetrics/PhpMetrics/issues/new

## Title: $errstr

## Message:

Hi,

This issue occured:

$errstr

**Environment**

+ PHP: $php
+ PhpMetrics: $phpmetrics
+ Operating System: $os

**Backtrace**

+ File: $errfile (line $errline)
$trace

**Debug**

```
$debug
```
EOT;

        $this->output->write($message);
        $this->terminate(1);
    }

    /**
     * @return $this
     */
    public function enable()
    {
        set_error_handler([$this, 'onError']);
        return $this;
    }

    /**
     * @param $status
     */
    protected function terminate($status) {
        exit($status);
    }

    /**
     * @param $debugKey
     * @param $value
     * @return $this
     */
    public function set($debugKey, $value)
    {
        $this->debug[$debugKey] = $value;
        return $this;
    }
}

