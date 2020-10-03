<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Issue;

use Hal\Component\Output\Output;
use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;

/**
 * @package Hal\Component\Issue
 */
class Issuer
{
    /**
     * @var array<string,mixed>
     */
    private $debug = [];

    /**
     * @var Output
     */
    private $output;

    /**
     * @param Output $output
     */
    public function __construct(Output $output)
    {
        $this->output = $output;
    }

    /**
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param mixed[] $errcontext
     * @return bool
     *
     * @throws \ErrorException
     */
    public function onError($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if (error_reporting() == 0) {
            return false;
        }
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

        $logfile = './phpmetrics-error.log';

        $message = <<<EOT

<error>We're sorry : an unexpected error occured.</error>

<question>Can you help us ?</question> Please open a new issue at https://github.com/phpmetrics/PhpMetrics/issues/new, and copy-paste the content
of this file: $logfile ?

Thanks for your help :)

EOT;

        $log = <<<EOT
## Title: $errstr

## Message:

Hi,

This issue occured:

$errstr

**Environment**

+ PHP: $php
+ PhpMetrics: $phpmetrics
+ Operating System: $os
+ File: $errfile (line $errline)

<details>
  <summary>Details</summary>
  ```
$trace


$debug
```
</details>

EOT;

        $this->output->write($message);

        $this->log($logfile, $log);
        $this->terminate(1);

        return true;
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
     * @return $this
     */
    public function disable()
    {
        restore_error_handler();
        return $this;
    }

    /**
     * @param int|string $status
     *
     * @return void
     */
    protected function terminate($status)
    {
        exit($status);
    }

    /**
     * @param string $logfile
     * @param mixed $log
     *
     * @return static
     */
    protected function log($logfile, $log)
    {
        if (getcwd() !== false && is_writable(getcwd())) {
            file_put_contents($logfile, $log);
        } else {
            $this->output->write($log);
        }
        return $this;
    }

    /**
     * @param string $debugKey
     * @param mixed $value
     * @return $this
     */
    public function set($debugKey, $value)
    {
        $this->debug[$debugKey] = $value;
        return $this;
    }

    /**
     * @param string $debugKey
     * @return $this
     */
    public function clear($debugKey)
    {
        unset($this->debug[$debugKey]);
        return $this;
    }
}
