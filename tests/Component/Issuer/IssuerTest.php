<?php

namespace Test\Hal\Component\Issue;

use Hal\Component\Issue\Issuer;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Output\Output;

/**
 * @group issue
 */
class IssuerTest extends \PHPUnit_Framework_TestCase
{

    public function testICanEnableIssuer()
    {
        $output = new TestOutput();
        $issuer = (new TestIssuer($output))->enable();
        $issuer->set('Firstname', 'Jean-François');

        try {
            echo new \stdClass();
        } catch (\Exception $e) {

        }

        $this->assertContains('Object of class stdClass could not be converted to string', $issuer->log);
        $this->assertContains('Operating System', $issuer->log);
        $this->assertContains('Details', $issuer->log);
        $this->assertContains('https://github.com/phpmetrics/PhpMetrics/issues/new', $output->output);
        $this->assertContains('Firstname: Jean-François', $issuer->log);
        $this->assertContains('IssuerTest.php (line 22)', $issuer->log);
    }

    public function testIssuerDisplayStatements()
    {
        $output = new TestOutput();
        $issuer = (new TestIssuer($output))->enable();
        $code = <<<EOT
<?php
class A{
   public function foo() {
   
   }
}
EOT;

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $stmt = $parser->parse($code);
        $issuer->set('code', $stmt);


        try {
            echo new \stdClass();
        } catch (\Exception $e) {

        }


        $this->assertContains('class A', $issuer->log);
    }
}

class TestIssuer extends Issuer
{
    public $log;

    protected function terminate($status)
    {
        throw new \RuntimeException('Terminated: ' . $status);
    }

    protected function log($logfile, $log)
    {
        $this->log = $log;
    }
}


class TestOutput extends Output
{
    public $output = '';

    public function clear()
    {
        $this->output = '';
    }

    protected function doWrite($message, $newline)
    {
        $this->output .= $message . ($newline ? "\n" : '');
    }
}
