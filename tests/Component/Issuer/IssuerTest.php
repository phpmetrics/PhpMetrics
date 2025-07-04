<?php

namespace Test\Hal\Component\Issue;

use Hal\Component\Ast\ParserFactoryBridge;
use Hal\Component\Issue\Issuer;
use Hal\Component\Output\TestOutput;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\RequiresPhp;
use Polyfill\TestCaseCompatible;

/**
 * @group issue
 */
class IssuerTest extends \PHPUnit\Framework\TestCase
{
    use TestCaseCompatible;
    /**
     * @requires PHP < 7.0
     */
    #[RequiresPhp('< 7.0')]
    public function testICanEnableIssuerPhp5(): void
    {
        $output = new TestOutput();
        $issuer = (new TestIssuer($output))->enable();
        $issuer->set('Firstname', 'Jean-François');

        try {
            trigger_error('Object of class stdClass could not be converted to string', E_USER_WARNING);
        } catch (\Exception $e) {
        }

        $this->assertStringContainsString('Object of class stdClass could not be converted to string', $issuer->log);
        $this->assertStringContainsString('Operating System', $issuer->log);
        $this->assertStringContainsString('Details', $issuer->log);
        $this->assertStringContainsString('https://github.com/phpmetrics/PhpMetrics/issues/new', $output->output);
        $this->assertStringContainsString('Firstname: Jean-François', $issuer->log);
        $this->assertStringContainsString('IssuerTest.php (line 29)', $issuer->log);
        $issuer->disable();
    }

    /**
     * @requires PHP < 7.0
     */
    public function testIssuerDisplayStatements(): void
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

        $parser = (new ParserFactoryBridge())->create();
        $stmt = $parser->parse($code);
        $issuer->set('code', $stmt);

        try {
            trigger_error('Object of class stdClass could not be converted to string', E_USER_ERROR);
        } catch (\Exception $e) {
        }

        $issuer->disable();
        $this->assertStringContainsString('class A', $issuer->log);
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
