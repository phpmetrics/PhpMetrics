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
 * Class Issuer
 * @package Hal\Component\Issue
 */
class Issuer
{
    /** @var array List of debug information where key is the type of information. */
    private $debug = [];

    /** @var Output The output system to print/log issues. */
    private $output;

    /**
     * Issuer constructor.
     * @param Output $output
     */
    public function __construct(Output $output)
    {
        $this->output = $output;
    }

    /**
     * Method that may be the internal error handler if the issuer is enabled.
     *
     * @param string $errStr Message of the error.
     * @param string $errFile Filename where the error occurred.
     * @param int $errLine Line number in the file where the error occurred.
     */
    public function onError($errStr, $errFile, $errLine)
    {
        if (0 === \error_reporting()) {
            return;
        }
        $php = \PHP_VERSION;
        $os = \PHP_OS;
        $phpMetrics = \getVersion();

        $traces = \debug_backtrace(0, 10);
        $trace = '';
        foreach ($traces as $c) {
            if (isset($c['file'])) {
                $trace .= \sprintf("+ %s (line %d)\n", $c['file'], $c['line']);
            }
        }

        $debug = '';
        foreach ($this->debug as $key => $value) {
            if ($value instanceof Node || \is_array($value)) {
                $value = (new Standard())->prettyPrint($value);
            }

            $debug .= \sprintf("%s: %s\n", $key, $value);
        }

        $logfile = './phpmetrics-error.log';

        $message = <<<EOT

<error>We're sorry : an unexpected error occurred.</error>
 
<question>Can you help us?</question> 
Please open a new issue at https://github.com/phpmetrics/PhpMetrics/issues/new, and copy-paste the content of this file:
 {$logfile}
 
Thanks for your help :)

EOT;

        $log = <<<EOT
## Title: {$errStr}

## Message:

Hi,

This issue occured:

{$errStr}

**Environment**

+ PHP: {$php}
+ PhpMetrics: {$phpMetrics}
+ Operating System: {$os}
+ File: {$errFile} (line {$errLine})

<details>
  <summary>Details</summary>
  ```
{$trace}

        
{$debug}
```
</details>

EOT;

        $this->output->write($message);

        $this->log($logfile, $log);
        $this->terminate(1);
    }

    /**
     * Enable the issuer. This will update the internal error handler to manage errors by this way.
     * @return $this
     */
    public function enable()
    {
        \set_error_handler([$this, 'onError']);
        return $this;
    }

    /**
     * Disable the issuer. This will restore the internal error handler.
     * @return $this
     */
    public function disable()
    {
        \restore_error_handler();
        return $this;
    }

    /**
     * End the script with the given status code.
     * @param int $status The status code to give to end the script.
     */
    protected function terminate($status)
    {
        exit($status);
    }

    /**
     * Log the issue file in the current working directory if possible, otherwise, just log with the output.
     * @param string $logfile The file name of the issue log.
     * @param string $log The log content.
     * @return $this
     */
    protected function log($logfile, $log)
    {
        if (\is_writable(\getcwd())) {
            \file_put_contents($logfile, $log);
        } else {
            $this->output->write($log);
        }
        return $this;
    }

    /**
     * Define or reset a debug key.
     * @param string $debugKey The debug key to be set.
     * @param mixed $value The value associated to the debug key.
     * @return $this
     */
    public function set($debugKey, $value)
    {
        $this->debug[$debugKey] = $value;
        return $this;
    }

    /**
     * Clean the value for a given debug key.
     * @param string $debugKey The debug key value to erase.
     * @return $this
     */
    public function clear($debugKey)
    {
        unset($this->debug[$debugKey]);
        return $this;
    }
}
