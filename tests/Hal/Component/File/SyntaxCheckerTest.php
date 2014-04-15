<?php
namespace Test\Hal\Component\File;

use Hal\Component\File\SyntaxChecker;


/**
 * @group file
 */
class SyntaxCheckerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider providesFiles
     */
    public function testErrorsInSyntaxAreFound($filename, $expected) {
        $checker = new SyntaxChecker();
        $result = $checker->isCorrect($filename);
        $this->assertEquals($expected, $result);
    }

    public function providesFiles() {
        return array(
            array(__DIR__.'/../../../resources/syntax/f1.php', true)
            , array(__DIR__.'/../../../resources/syntax/f2.php', false)
        );
    }

}