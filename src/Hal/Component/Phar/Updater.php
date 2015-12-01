<?php

/*
 * (c) Jean-François Lépine <https://twitter.com/Halleck45>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hal\Component\Phar;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * phar updater
 *
 * @author Jean-François Lépine <https://twitter.com/Halleck45>
 */
class Updater {

    const LATEST = 'latest';

    /**
     * @var string
     */
    private $url = 'https://github.com/Halleck45/PhpMetrics/raw/%s/build/phpmetrics.phar';

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Updater constructor.
     * @param $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param string $version
     * @return string
     */
    public function updates($version = self::LATEST) {
        $tag = $version;
        if(self::LATEST == $version) {
            $tag = 'master';
        }

        if (ini_get('phar.readonly') == true) {
            throw new \RuntimeException('Unable to update the PHAR, phar.readonly is set, use \'-d phar.readonly=0\'');
        }

        if (ini_get('allow_url_fopen') == false) {
            throw new \RuntimeException('Unable to update the PHAR, allow_url_fopen is not set, use \'-d allow_url_fopen=1\'');
        }

        $currentPharLocation = \Phar::running(false);
        if(!file_exists($currentPharLocation) ||strlen($currentPharLocation) == 0) {
            throw new \LogicException("You're not currently using Phar. If you have installed PhpMetrics with Composer, please updates it using Composer.");
        }

        if(!is_writable($currentPharLocation)) {
            throw new \RuntimeException(sprintf('%s is not writable', $currentPharLocation));
        }

        // downloading
        $url = sprintf($this->url, $tag);
        $ctx = stream_context_create();
        stream_context_set_params($ctx, array("notification" => array($this, 'stream_notification_callback')));
        $content = file_get_contents($url, false, $ctx);

        // replacing file
        if(!$content) {
            throw new \RuntimeException('Download failed');
        }

        // check if all is OK
        $tmpfile = tempnam(sys_get_temp_dir(), 'phar');
        file_put_contents($tmpfile, $content);
        $output = shell_exec(sprintf('"%s" "%s" --version', PHP_BINARY, $tmpfile));
        if(!preg_match('!(v\d+\.\d+\.\d+)!', $output, $matches)) {
            throw new \RuntimeException('Phar is corrupted. Please retry');
        }

        // compare versions
        $downloadedVersion = $matches[1];
        if(self::LATEST !==$version &&$downloadedVersion !== $version) {
            throw new \RuntimeException('Incorrect version. Please retry');
        }

        // at this step, all is ok
        file_put_contents($currentPharLocation, $content);
        return $version;

    }

    /**
     * Stream downloading
     *
     * @param $notification_code
     * @param $severity
     * @param $message
     * @param $message_code
     * @param $bytes_transferred
     * @param $bytes_max
     */
    public function stream_notification_callback($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) {
        static $filesize = null;
        static $progress = null;

        switch($notification_code) {
            case STREAM_NOTIFY_AUTH_REQUIRED:
            case STREAM_NOTIFY_AUTH_RESULT:
                break;
            case STREAM_NOTIFY_CONNECT:
                if(!$progress) {
                    $this->output->writeln(sprintf("<info>Downloading</info>"));
                }
                break;

            case STREAM_NOTIFY_FILE_SIZE_IS:
                $filesize = $bytes_max;
                $progress = new ProgressBar($this->output);
                $progress->start($filesize / 100);
                break;

            case STREAM_NOTIFY_PROGRESS:
                if ($bytes_transferred > 0) {
                    if (!isset($filesize)) {
                        $this->output->writeln(sprintf("<info>Unknown file size.. %2d kb done..</info>", $bytes_transferred/1024));
                    } else {
                        $progress->setProgress($bytes_transferred / 100);
                    }
                }
                break;

            case STREAM_NOTIFY_COMPLETED:
            case STREAM_NOTIFY_FAILURE:
                if($progress) {
                    $progress->clear();
                    $progress->finish();
                }
                break;
        }
    }


}