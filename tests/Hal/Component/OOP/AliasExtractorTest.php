<?php
namespace Test\Hal\Component\OOP\Extractor;
use Hal\Component\OOP\Extractor\AliasExtractor;
use Hal\Component\Token\TokenCollection;

/**
 * @group oop
 */
class AliasExtractorTest extends \PHPUnit_Framework_TestCase {

    public function testExtractUseStatement() {
        $n = 10;
        $tokens = new TokenCollection(array());

        $searcher = $this->getMockBuilder('\Hal\Component\OOP\Extractor\Searcher')->disableOriginalConstructor()->getMock();
        $searcher->expects($this->once())
            ->method('getUnder')
            ->with(array(";"), $n, $tokens)
            ->will($this->returnValue('use Hal\Component\OOP\Extractor\AliasExtractor'));

        $extractor = new AliasExtractor($searcher);
        $actual = $extractor->extract($n, $tokens);

        $expected = (object)array('name' => 'Hal\Component\OOP\Extractor\AliasExtractor', 'alias' => 'Hal\Component\OOP\Extractor\AliasExtractor');
        $this->assertEquals($expected, $actual);
    }

    public function testExtractUseStatementWithAlias() {
        $n = 10;
        $tokens = new TokenCollection(array());

        $searcher = $this->getMockBuilder('\Hal\Component\OOP\Extractor\Searcher')->disableOriginalConstructor()->getMock();
        $searcher->expects($this->once())
            ->method('getUnder')
            ->with(array(";"), $n, $tokens)
            ->will($this->returnValue('use Hal\Component\OOP\Extractor\AliasExtractor as AliasExtractor'));

        $extractor = new AliasExtractor($searcher);
        $actual = $extractor->extract($n, $tokens);

        $expected = (object)array('name' => 'Hal\Component\OOP\Extractor\AliasExtractor', 'alias' => 'AliasExtractor');
        $this->assertEquals($expected, $actual);
    }

    public function testExtractAnonymousFunction() {
        $n = 10;
        $tokens = new TokenCollection(array());

        $searcher = $this->getMockBuilder('\Hal\Component\OOP\Extractor\Searcher')->disableOriginalConstructor()->getMock();
        $searcher->expects($this->once())
            ->method('getUnder')
            ->with(array(";"), $n, $tokens)
            ->will($this->returnValue('use ($variable) { if ($variable > 0) return $variable'));

        $extractor = new AliasExtractor($searcher);
        $actual = $extractor->extract($n, $tokens);

        $expected = (object)array('name' => null, 'alias' => null);
        $this->assertEquals($expected, $actual);
    }

    public function testExtractAnonymousFunctionWithoutWithspace() {
        $n = 10;
        $tokens = new TokenCollection(array());

        $searcher = $this->getMockBuilder('\Hal\Component\OOP\Extractor\Searcher')->disableOriginalConstructor()->getMock();
        $searcher->expects($this->once())
            ->method('getUnder')
            ->with(array(";"), $n, $tokens)
            ->will($this->returnValue('use($variable) { if ($variable > 0) return $variable'));

        $extractor = new AliasExtractor($searcher);
        $actual = $extractor->extract($n, $tokens);

        $expected = (object)array('name' => null, 'alias' => null);
        $this->assertEquals($expected, $actual);
    }

}
