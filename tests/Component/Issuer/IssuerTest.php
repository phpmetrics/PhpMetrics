<?php

namespace Test\Hal\Component\Issue;

use Hal\Component\Issue\Issuer;
use Hal\Component\Output\TestOutput;
use PhpParser\ParserFactory;

/**
 * @group issue
 */
class IssuerTest extends \PHPUnit\Framework\TestCase
{
    public function testICanEnableIssuerPhp5()
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->markTestSkipped('Skipped for version 7');
        }

        $output = new TestOutput();
        $issuer = (new TestIssuer($output))->enable();
        $issuer->set('Firstname', 'Jean-François');

        try {
            trigger_error('Object of class stdClass could not be converted to string', E_USER_ERROR);
        } catch (\Exception $e) {
        }

        $this->assertContains('Object of class stdClass could not be converted to string', $issuer->log);
        $this->assertContains('Operating System', $issuer->log);
        $this->assertContains('Details', $issuer->log);
        $this->assertContains('https://github.com/phpmetrics/PhpMetrics/issues/new', $output->output);
        $this->assertContains('Firstname: Jean-François', $issuer->log);
        $this->assertContains('IssuerTest.php (line 25)', $issuer->log);
        $issuer->disable();
    }

    public function testICanEnableIssuerPhp7()
    {
        if (PHP_VERSION_ID < 70000) {
            $this->markTestSkipped('Skipped for version 5');
        }

        $output = new TestOutput();
        $issuer = (new TestIssuer($output))->enable();
        $issuer->set('Firstname', 'Jean-François');

        try {
            trigger_error('Object of class stdClass could not be converted to string', E_USER_ERROR);
        } catch (\Throwable $e) {
        }

        $this->assertContains('Object of class stdClass could not be converted to string', $issuer->log);
        $this->assertContains('Operating System', $issuer->log);
        $this->assertContains('Details', $issuer->log);
        $this->assertContains('https://github.com/phpmetrics/PhpMetrics/issues/new', $output->output);
        $this->assertContains('Firstname: Jean-François', $issuer->log);
        $this->assertContains('IssuerTest.php (line 49)', $issuer->log);
        $issuer->disable();
    }

    public function testIssuerDisplayStatementsOnPhp5()
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->markTestSkipped('Skipped for version 5');
        }

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

        $issuer->disable();
        $this->assertContains('class A', $issuer->log);
    }

    public function testIssuerDisplayStatementsOnPhp7()
    {
        if (PHP_VERSION_ID < 70000) {
            $this->markTestSkipped('Skipped for version 7');
        }

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
        } catch (\Throwable $e) {
        }

        $issuer->disable();
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
